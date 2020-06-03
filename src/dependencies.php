<?php

use App\Services\PostService;
use App\Markdown;
use Slim\Container;

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
    return new Twig\Environment($loader, [
        __DIR__ . '/../var/cache'
    ]);
};

$container['markdown'] = function($c) {
    return new Markdown();
};

$container['PostService'] = function(Container $c) {
    return new PostService($c->get('markdown'));
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};