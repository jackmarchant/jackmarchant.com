<?php

namespace App;

use DI\Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\PostService;
use Psr\Log\LoggerInterface;

class BlogPostController
{
    /** @var LoggerInterface */
    private $logger;

    /** @var array */
    private $settings;
    
    /** @var PostService */
    private $postService;
    
    private $renderer;

    public function __construct(Container $container)
    {
        $this->logger = $container->get('logger');
        $this->renderer = $container->get('renderer');
        $this->settings = $container->get('settings');
        $this->postService = $container->get('PostService');
    }

    public function __invoke(Request $request, Response $response, $args): Response
    {
        $this->logger->info('blog post handler dispatched');

        $post = ['title' => 'Page not found'];
        $adjacent = ['newer' => null, 'older' => null];

        if (isset($args['post'])) {
            $foundPost = $this->postService->findPostByPath($args['post']);
            if (!empty($foundPost)) {
                $post = $foundPost;
                $adjacent = $this->postService->findAdjacentPosts($args['post']);
            }
        }

        if ($this->wantsMarkdown($request)) {
            $markdown = $this->renderMarkdown($post);
            $response->getBody()->write($markdown);
            return $response
                ->withHeader('Content-Type', 'text/markdown; charset=UTF-8')
                ->withHeader('X-Markdown-Tokens', (string) str_word_count(strip_tags($markdown)));
        }

        $body = $this->renderer->render('index.twig', [
            'post' => $post,
            'newer' => $adjacent['newer'],
            'older' => $adjacent['older'],
            'settings' => $this->settings,
        ]);
        $response->getBody()->write($body);

        return $response;
    }

    private function wantsMarkdown(Request $request): bool
    {
        $accept = strtolower($request->getHeaderLine('Accept'));
        return strpos($accept, 'text/markdown') !== false;
    }

    private function renderMarkdown(array $post): string
    {
        if (empty($post['url'])) {
            return "# page not found\n";
        }

        $lines = [
            '# ' . $post['title'],
            '',
            '_Published ' . $post['date'] . '_',
            '',
        ];

        if (!empty($post['tldr'])) {
            $lines[] = '> TL;DR: ' . trim($post['tldr']);
            $lines[] = '';
        }

        $bodyMarkdown = trim(isset($post['markdown']) ? $post['markdown'] : '');
        if (!empty($bodyMarkdown)) {
            $lines[] = $bodyMarkdown;
        }

        return implode("\n", $lines) . "\n";
    }
}
