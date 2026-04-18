<?php

namespace App;

use DI\Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Logging\LoggerInterface;
use App\Services\PostService;

class BlogListController
{
    private const DISCOVERY_LINK = '</.well-known/api-catalog>; rel="api-catalog"';

    /** @var LoggerInterface */
    private $logger;

    private $renderer;

    /** @var array */
    private $settings;

    /** @var PostService */
    private $postService;

    public function __construct(Container $container)
    {
        $this->logger = $container->get('logger');
        $this->renderer = $container->get('renderer');
        $this->postService = $container->get('PostService');
        $this->settings = $container->get('settings');
    }

    public function __invoke(Request $request, Response $response, $args): Response
    {
        $this->logger->info('blog list handler dispatched');

        $posts = $this->postService->getAllPostListings();
        $response = $response->withAddedHeader('Link', self::DISCOVERY_LINK);

        if ($this->wantsMarkdown($request)) {
            $markdown = $this->renderMarkdown($posts);
            $response->getBody()->write($markdown);

            return $response
                ->withHeader('Content-Type', 'text/markdown; charset=UTF-8')
                ->withHeader('X-Markdown-Tokens', (string) str_word_count(strip_tags($markdown)));
        }

        $response->getBody()->write(
            $this->renderer->render('index.twig', [
                'posts' => $posts,
                'tags' => $this->postService->getAllTags($posts),
                'settings' => $this->settings,
            ])
        );
        return $response;
    }

    private function wantsMarkdown(Request $request): bool
    {
        $accept = strtolower($request->getHeaderLine('Accept'));
        return strpos($accept, 'text/markdown') !== false;
    }

    private function renderMarkdown(array $posts): string
    {
        $lines = [
            '# jack marchant',
            '',
            'Latest posts:',
            '',
        ];

        foreach ($posts as $post) {
            $summary = trim(strip_tags(isset($post['tldr']) ? $post['tldr'] : $post['blurb']));
            $lines[] = sprintf('- [%s](%s) (%s)', $post['title'], $post['url'], $post['date']);
            if (!empty($summary)) {
                $lines[] = sprintf('  - %s', $summary);
            }
        }

        return implode("\n", $lines) . "\n";
    }
}
