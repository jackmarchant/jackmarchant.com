<?php

namespace App;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
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

        if (isset($args['post'])) {
            $post = $this->postService->findPostByPath($args['post']);
        }

        $body = $this->renderer->render('index.twig', [
            'post' => $post,
            'settings' => $this->settings,
            'listings' => $this->postService->getAllPostListings(),
        ]);
        $response->getBody()->write($body);

        return $response;
    }
}
