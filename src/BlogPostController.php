<?php

namespace App;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Parsedown;
use DateTime;

class BlogPostController
{
    private $logger;
    private $renderer;
    private $markdown;
    private $settings;

    public function __construct(Container $container)
    {
        $this->logger = $container->get('logger');
        $this->renderer = $container->get('renderer');
        $this->markdown = $container->get('markdown');
        $this->settings = $container->get('settings');
    }

    public function __invoke(Request $request, Response $response, $args): Response
    {
        $this->logger->info('blog post handler dispatched');

        $post = [];
    
        if (isset($args['post'])) {
            $filepath = sprintf('/../content/%s/index.md', $args['post']);
            $content = file_get_contents(__DIR__ . $filepath);
            $exploded = explode('---', $content);
            $metadata = $this->parseMetadata($exploded[1]);
            $post = [
                'title' => $metadata['title'],
                'date' => (new DateTime($metadata['date']))->format('F d, Y'),
                'content' => $this->markdown->text($exploded[2]),
            ];
            $this->logger->info($post['content']);
        }
    
        $response->getBody()->write(
            $this->renderer->render('index.twig', ['post' => $post, 'settings' => $this->settings])
        );
        return $response;
    }

    protected function parseMetadata($metadata) {
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
}
