<?php

namespace Intec\Router;

use Exception;
use Intec\Router\CallableResolver;

use Pimple\Container as PimpleContainer;
use Pimple\Psr11\Container;
use Psr\Container\ContainerInterface;
use Intec\Router\Request;
use Intec\Router\Response;

class SimpleRouter
{
    private static $routes = [];
    private static $defaultMiddlewares = [];
    private static $notFoundFallback;
    private static $errorFallback;
    private static $container;

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
        $response = new Response();

        if (!$pimpleContainer) {
            $pimpleContainer = new Container(new PimpleContainer());
        }

        self::$container = $pimpleContainer;
        $callableResolver = new CallableResolver($pimpleContainer);

        return self::matchRequest($request, $response, $callableResolver);
    }

    private static function matchRequest($request, $response, $callableResolver)
    {
        try {
            $path = $request->getUri()->getPath();
            foreach (self::$routes as $pattern => $obj) {
                if (preg_match($pattern, $path, $params)) {
                    array_shift($params);
                    $request->setUrlParams($params);

                    // Middlewares
                    $middlewares = array_merge(self::$defaultMiddlewares, $obj['middlewares']);
                    while ($mid = array_shift($middlewares)) {
                        $callable = $callableResolver->resolve($mid);
                        $response = call_user_func($callable, $request, $response);
                        if(!$response) {
                            exit;
                        }
                    }

                    // Controller
                    $callable = $callableResolver->resolve($obj['callback']);
                    $response = call_user_func($callable, $request, $response, $params);
                    self::sendResponse($response);
                }
            }

            // 404 Middleware
            $fbck = self::$notFoundFallback;
            if ($fbck) {
                $callable = $callableResolver->resolve($fbck);
                $response = call_user_func($callable, $request, $response);
                self::sendResponse($response);
            }
        } catch (\Throwable $err) {
            $fbck = self::$errorFallback;
            if ($fbck) {
                $callable = $callableResolver->resolve($fbck);
                $response = call_user_func($callable, $request, $response, null, $err);
                self::sendResponse($response);
            }
        }
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
