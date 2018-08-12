<?php

namespace Intec\Router;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Intec\Router\SimpleRouter;

class RequestHandler implements RequestHandlerInterface
{
    /**
     * Handle the request and return a response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $next = SimpleRouter::nextMiddleware();
        if($next) {
            return $next->process($request, $this);
        }
        return $this->getResponse();
    }

    public function getResponse()
    {
        return SimpleRouter::createResponse();
    }
}

