<?php
// Routes

use App\BlogListController;
use App\BlogPostController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Middlewares\TrailingSlash;

$app->add(new TrailingSlash(false)); // true adds the trailing slash (false removes it)

$app->get('/{post}', BlogPostController::class);
$app->get('/', BlogListController::class);