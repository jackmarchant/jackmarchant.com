<?php

use App\Services\PostService;
use App\Markdown;
use \Psr\Container\ContainerInterface;

$container = $app->getContainer();

$container->set('settings', function (ContainerInterface $c) {
    return require __DIR__ . '/settings.php';
});

// view renderer
$container->set('renderer', function (ContainerInterface $c) {
    $loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
    return new Twig\Environment($loader, [
        __DIR__ . '/../var/cache'
    ]);
});

$container->set('markdown', function(ContainerInterface $c) {
    return new Markdown();
});

$container->set('PostService', function(ContainerInterface $c) {
    return new PostService($c->get('markdown'));
});

// monolog
$container->set('logger', function (ContainerInterface $c) {
    $logger = new Monolog\Logger('jackmarchant');
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ . '/../logs/app.log', Monolog\Logger::DEBUG));
    return $logger;
});