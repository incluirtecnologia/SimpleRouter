<?php

require '../vendor/autoload.php';

use Intec\Router\SimpleRouter;

SimpleRouter::setRoutes([
    [
        'pattern' => '/test',
        'callback' => function($request, $response, $params) {
            return $response->json(200, 'hello!!!', $params);
        }
    ]
]);

SimpleRouter::match($_SERVER['REQUEST_URI']);