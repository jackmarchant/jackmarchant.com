<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
    $twig = new Twig\Environment($loader, [
        // __DIR__ . '/../var/cache'
    ]);
    return $twig;
};

$container['markdown'] = function($c) {
    return new Parsedown();
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};