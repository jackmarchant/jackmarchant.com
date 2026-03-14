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

    private function getTemplate(Request $request): string
    {
        $params = $request->getQueryParams();
        $validThemes = ['green-terminal', 'amber-crt', 'modern-ide'];
        if (isset($params['theme']) && in_array($params['theme'], $validThemes)) {
            return 'theme-' . $params['theme'] . '.twig';
        }
        return 'index.twig';
    }

    public function __invoke(Request $request, Response $response, $args): Response
    {
        $this->logger->info('blog post handler dispatched');

        $post = ['title' => 'Page not found'];

        if (isset($args['post'])) {
            $post = $this->postService->findPostByPath($args['post']);
        }

        $body = $this->renderer->render($this->getTemplate($request), [
            'post' => $post,
            'settings' => $this->settings,
            'listings' => $this->postService->getAllPostListings(),
        ]);
        $response->getBody()->write($body);

        return $response;
    }
}
