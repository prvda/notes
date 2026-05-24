<?php

declare(strict_types=1);

use App\Core\Router;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$router = new Router();

require base_path('routes.php');

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

