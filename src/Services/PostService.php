<?php

namespace App\Services;

use App\MarkdownParserInterface;
use DateTime;

class PostService
{
    const CONTENT_PATH = '/../../content';

    /** @var MarkdownParserInterface */
    private $parser;

    public function __construct(MarkdownParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Find a post by its slug
     * 
     * @param string $path
     * @return array
     */
    public function findPostByPath(string $path): array
    {
        $post = [];
        $filepath = __DIR__ . sprintf('%s/%s/index.md', self::CONTENT_PATH, $path);
        if ($this->postExists($filepath)) {
            $postRaw = $this->readPost($filepath);
            $metadata = $this->parseMetadata($postRaw['meta']);
            $paragraphs = array_values(array_filter(explode("\n", $postRaw['body']), function ($content) {
                return !empty($content);
            }));
            $blurb = strip_tags($this->parser->parse($paragraphs[0] ?? ''));
            $post = [
                'title' => trim($metadata['title']),
                'date' => (new DateTime($metadata['date']))->format('F d, Y'),
                'content' => $this->parser->parse($postRaw['body']),
                'blurb' => $blurb,
                'tldr' => isset($metadata['tldr']) ? trim($metadata['tldr']) : '',
                'url' => '/' . $path,
            ];
        }
        
        return $post;
    }

    /**
     * Get all Post Listings
     *
     * @return array
     */
    public function getAllPostListings(): array
    {
        $folders = array_filter(glob(__DIR__ . self::CONTENT_PATH . '/*'), 'is_dir');
        $posts = [];
        
        foreach ($folders as $folder) {
            $paths = explode('/', $folder);
            $path = end($paths);
            $metadata = $this->getMetadata($path);
            $posts[strtotime($metadata['date'])] = [
                'title' => $metadata['title'],
                'url' => sprintf('/%s', $path),
                'date' => (new DateTime($metadata['date']))->format('Y-m'),
                'blurb' => $metadata['blurb'],
                'tags' => $metadata['tags'],
            ];
        }

        krsort($posts);

        return $posts;
    }

    protected function getMetadata($path)
    {
        $content = file_get_contents(__DIR__ . sprintf('%s/%s/index.md', self::CONTENT_PATH, $path));
        $exploded = explode('---', $content);
        $metadata = $this->parseMetadata($exploded[1]);
        $summary = isset($metadata['tldr']) ? trim($metadata['tldr']) : '';
        if (!empty($summary)) {
            $blurb = $this->parser->parse($summary);
        } else {
            $md = array_filter(explode("\n", $exploded[2]), function ($content) {
                return !empty($content);
            });
            $blurb = $this->parser->parse(array_shift($md));
        }
        $title = isset($metadata['title']) ? trim($metadata['title']) : str_replace('-', ' ', $path);
        $tags = $this->parseTags(isset($metadata['tags']) ? $metadata['tags'] : '');
        if (empty($tags)) {
            $tags = $this->inferTags(sprintf('%s %s %s', $path, $title, strip_tags($blurb)));
        }

        return [
            'title' => $title,
            'date' => $metadata['date'],
            'blurb' => $blurb,
            'tags' => $tags,
        ];
    }

    protected function parseMetadata(string $metadata): array
    {
        $lines = explode("\n", $metadata);
        $data = [];
        foreach ($lines as $line) {
            $pos = strpos($line, ':');
            $title = substr($line, 0, $pos);
            $content = substr($line, $pos + 1);
    
            if ($title) {
                $data[$title] = str_replace('"', '', $content);
            }
        }
        return $data;
    }

    protected function parseTags(string $rawTags): array
    {
        if (empty(trim($rawTags))) {
            return [];
        }

        $parts = preg_split('/[,|]/', $rawTags);
        $tags = array_values(array_unique(array_filter(array_map(function ($tag) {
            return strtolower(trim($tag));
        }, $parts))));

        return $tags;
    }

    /**
     * Get all unique tags from a posts array, sorted alphabetically.
     *
     * @param array $posts
     * @return array
     */
    public function getAllTags(array $posts): array
    {
        $seen = [];
        foreach ($posts as $post) {
            foreach ($post['tags'] as $tag) {
                $seen[$tag] = true;
            }
        }
        $tags = array_keys($seen);
        sort($tags);
        return $tags;
    }

    protected function inferTags(string $content): array
    {
        $normalizedContent = strtolower($content);
        $tagPatterns = [
            'elixir' => '/\b(elixir|erlang|genserver|ecto|phoenix)\b/',
            'ai' => '/\b(ai|llm|copilot|autocomplete)\b/',
            'database' => '/\b(database|sql|query|postgres|index|pagination)\b/',
            'testing' => '/\b(tests?|testing|tdd|spec|assert)\b/',
            'php' => '/\bphp\b/',
            'career' => '/\b(interview|interviewing|interviewee|candidate|hiring|remote)\b/',
            'practices' => '/code\s+review|pull\s+request|\brefactor(ing)?\b|tech\s+debt|technical\s+debt|design\s+pattern|dependency\s+injection|feature\s+flags?|\bmaintainab(le|ility)?\b/',
        ];
        $tags = [];

        foreach ($tagPatterns as $tag => $pattern) {
            if (preg_match($pattern, $normalizedContent)) {
                $tags[] = $tag;
            }
        }

        if (empty($tags)) {
            $tags[] = 'engineering';
        }

        return $tags;
    }

    protected function postExists(string $path): bool
    {
        return file_exists($path);
    }

    protected function readPost(string $filepath): array
    {
        $post = explode('---', file_get_contents($filepath));
        return [
            'meta' => $post[1],
            'body' => $post[2],
        ];
    }
}
