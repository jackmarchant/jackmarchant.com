<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

/**
 * Slim 4.x does not ship with a container library. It supports all PSR-11 implementations such as PHP-DI
 * To install PHP-DI `composer require php-di/php-di`
 */

use Slim\Factory\AppFactory;

$container = new \DI\Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();
