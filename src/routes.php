<?php
// Routes

use App\BlogListController;
use App\BlogPostController;

$app->get('/{post}', BlogPostController::class);
$app->get('/', BlogListController::class);