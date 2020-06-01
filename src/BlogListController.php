<?php

namespace App;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use DateTime;

class BlogListController
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
        $this->logger->info('blog list handler dispatched');

        $folders = array_filter(glob(__DIR__ . '/../content/*'), 'is_dir');
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

        $response->getBody()->write(
            $this->renderer->render('index.twig', ['posts' => $posts, 'settings' => $this->settings])
        );
        return $response;
    }

    protected function getMetadata($path)
    {
        $content = file_get_contents(__DIR__ . '/../content/' . $path . '/index.md');
        $exploded = explode('---', $content);
        $metadata = $this->parseMetadata($exploded[1]);
        $md = array_filter(explode("\n", $exploded[2]), function ($content) {
            return !empty($content);
        });
        $blurb = $this->markdown->text(array_shift($md));

        return [
            'date' => $metadata['date'],
            'blurb' => $blurb,
        ];
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
