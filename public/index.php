<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Request;
use App\Core\Router;

$router = new Router();
(require dirname(__DIR__) . '/app/Config/routes.php')($router);

$router->dispatch(new Request());
