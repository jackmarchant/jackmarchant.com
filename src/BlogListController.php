<?php

namespace App;

use DI\Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Logging\LoggerInterface;
use App\Services\PostService;

class BlogListController
{
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

        $response->getBody()->write(
            $this->renderer->render('index.twig', [
                'posts' => $posts, 
                'settings' => $this->settings,
            ])
        );
        return $response;
    }
}
