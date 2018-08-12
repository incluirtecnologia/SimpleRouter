# Simple Router for php >= 5.6.

[![Build Status](https://travis-ci.org/incluirtecnologia/SimpleRouter.svg?branch=master)](https://travis-ci.org/incluirtecnologia/SimpleRouter)

## How to Install:

`composer require 'intec/simple-router';`

## How to Use:

```php
use Intec\Router\SimpleRouter;

use Intec\Router\SimpleRouter;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

SimpleRouter::setRoutes([
    [
        'pattern' => '/my/name/is/([a-zA-Z]*)/age/([0-9]{1,2})',
        'middlewares' => [
            new Class implements MiddlewareInterface {
                public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler) : ResponseInterface
                {
                    // if you want to block the request at this point use
                    // $response = $requestHandler->getResponse()
                    // fill the response
                    // return $response

                    $response = $requestHandler->handle($request);
                    $response->getBody()->write("Middleware 1<br>");
                    return $response;
                }
            },
            new Class implements MiddlewareInterface {
                public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler) : ResponseInterface
                {
                    // if you want to block the request at this point use
                    // $response = $requestHandler->getResponse()
                    // fill the response
                    // return $response

                    $response = $requestHandler->handle($request);
                    $response->getBody()->write("Middleware 2<br>");
                    return $response;
                }
            },
        ],
        'callback' => function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
            $name = $params[0];
            $age = $params[1];
            $data = sprintf("Name: %s. Age: %s years<br>", $name, $age);
            $response->getBody()->write($data);
            return $response;
        }
    ],
]);

SimpleRouter::run();
```
