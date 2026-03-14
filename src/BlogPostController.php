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

        if (isset($args['post'])) {
            $post = $this->postService->findPostByPath($args['post']);
        }

        $currentUrl = isset($args['post']) ? sprintf('/%s', $args['post']) : null;
        $primaryTag = !empty($post['tags']) ? $post['tags'][0] : null;
        $allListings = $this->postService->getAllPostListings();

        $listings = array_values(array_filter($allListings, function ($listing) use ($currentUrl, $primaryTag) {
            if ($currentUrl !== null && $listing['url'] === $currentUrl) {
                return false;
            }
            if ($primaryTag !== null && !in_array($primaryTag, $listing['tags'], true)) {
                return false;
            }
            return true;
        }));

        $body = $this->renderer->render('index.twig', [
            'post' => $post,
            'settings' => $this->settings,
            'listings' => $listings,
            'listingsCategory' => $primaryTag,
        ]);
        $response->getBody()->write($body);

        return $response;
    }
}
