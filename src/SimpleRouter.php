<?php

namespace Intec\Router;

use Exception;
use Intec\Router\CallableResolver;

use Pimple\Container as PimpleContainer;
use Pimple\Psr11\Container;
use Psr\Container\ContainerInterface;
use Intec\Router\Request;
use Intec\Router\Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SimpleRouter
{
    private static $routes = [];
    private static $defaultMiddlewares = [];
    private static $notFoundFallback;
    private static $errorFallback;
    private static $container;
    private static $middlewares;
    private static $callableResolver;
    private static $urlParams;

    private function __construct()
    {
    }
    private function __clone()
    {
    }

    public static function setNotFoundFallback($fallback)
    {
        self::$notFoundFallback = $fallback;
    }

    public static function setErrorFallback($fallback)
    {
        self::$errorFallback = $fallback;
    }

    private static function buildPattern($pattern)
    {
        return '/^' . str_replace('/', '\/', $pattern) . '$/';
    }

    public static function setDefaultMiddlewares($middlewares)
    {
        self::$defaultMiddlewares = $middlewares;
    }

    public static function add($pattern, $callback, array $middlewares = [])
    {
        $pattern = self::buildPattern($pattern);
        self::$routes[$pattern] = [
            'callback' => $callback,
            'middlewares' => $middlewares,
        ];
    }

    public static function match($str, ContainerInterface $pimpleContainer = null)
    {
        self::run($pimpleContainer);
    }

    public static function run(ContainerInterface $pimpleContainer = null)
    {
        $request = Request::createFromGlobals($_SERVER);

        if (!$pimpleContainer) {
            $pimpleContainer = new Container(new PimpleContainer());
        }

        self::$container = $pimpleContainer;
        self::$callableResolver = new CallableResolver($pimpleContainer);

        return self::matchRequest($request);
    }

    private static function matchRequest($request)
    {
        $path = $request->getUri()->getPath();
        $requestHandler = new RequestHandler();
        $defMiddlewares = array_merge([self::$errorFallback, self::$notFoundFallback], self::$defaultMiddlewares);
        foreach (self::$routes as $pattern => $obj) {
            if (preg_match($pattern, $path, $params)) {
                array_shift($params);
                self::$urlParams = $params;
                // Middlewares 500, 404, default middlewares, route specific middlewares, controller action
                self::$middlewares = array_filter(array_merge($defMiddlewares, $obj['middlewares'] ?? [], [self::createMiddlewareController($obj['callback'])]));
                $response = $requestHandler->handle($request);
                self::sendResponse($response);
            }
        }
    }

    private static function createMiddlewareController($objectToResolve)
    {
        return new class(self::$callableResolver, $objectToResolve, self::$urlParams) implements MiddlewareInterface {
            private $cResolver;
            private $objectToResolve;
            private $urlParams;

            public function __construct($cResolver, $objectToResolve, $urlParams)
            {
                $this->cResolver = $cResolver;
                $this->objectToResolve = $objectToResolve;
                $this->urlParams = $urlParams;
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler) : ResponseInterface
            {
                $callable = $this->cResolver->resolve($this->objectToResolve);
                $response = $requestHandler->getResponse();
                return call_user_func($callable, $request, $response, $this->urlParams);
            }
        };
    }

    public static function nextMiddleware()
    {
        if ($mid = array_shift(self::$middlewares)) {
            if(!is_object($mid)) {
                return self::$callableResolver->resolve($mid);
            }
            return $mid;
        }

        return null;
    }

    public static function createResponse()
    {
        return new Response();
    }

    public static function getUrlParams()
    {
        return self::$urlParams;
    }

    private static function sendResponse($response)
    {
        if (headers_sent() || !$response) {
            exit;
        }

        $statusLine = sprintf(
            'HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        header($statusLine, true);

        foreach ($response->getHeaders() as $name => $values) {
            $responseHeader = sprintf('%s: %s', $name, $response->getHeaderLine($name));
            header($responseHeader, false);
        }

        echo $response->getBody();
        exit;
    }

    private static function resolve($fn)
    {
        if (is_callable($fn)) {
            return $fn;
        }

        if (!is_string($fn)) {
            self::assertCallable($fn);
            throw new Exception(sprintf("%s not exists"), json_encode($fn));
        }
    }

    public static function setRoutes(array $routes)
    {
        self::clear();
        self::addRoutes($routes);
    }

    public static function addRoutes(array $routes)
    {
        foreach ($routes as $route) {
            if (empty($route['middlewares'])) {
                self::add($route['pattern'], $route['callback']);
            } else {
                self::add($route['pattern'], $route['callback'], $route['middlewares']);
            }
        }
    }

    public static function clear()
    {
        self::$routes = [];
    }

    public static function hasRoute($pattern)
    {
        return array_key_exists(self::buildPattern($pattern), self::$routes);
    }

    public static function redirectTo($route)
    {
        header('Location: ' . $route);
        exit;
    }

    public static function getContainer()
    {
        return self::$container;
    }
}
