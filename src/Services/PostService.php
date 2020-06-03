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
            $post = [
                'title' => $metadata['title'],
                'date' => (new DateTime($metadata['date']))->format('F d, Y'),
                'content' => $this->parser->parse($postRaw['body']),
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
                'title' => str_replace('-', ' ', $path),
                'url' => sprintf('/%s', $path),
                'date' => (new DateTime($metadata['date']))->format('F d, Y'),
                'blurb' => $metadata['blurb'],
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
        $md = array_filter(explode("\n", $exploded[2]), function ($content) {
            return !empty($content);
        });
        $blurb = $this->parser->parse(array_shift($md));

        return [
            'date' => $metadata['date'],
            'blurb' => $blurb,
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