<?php

namespace Intec\Router;

use Exception;
use Intec\Router\CallableResolver;

use Pimple\Container as PimpleContainer;
use Pimple\Psr11\Container;
use Psr\Container\ContainerInterface;

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
        $request = new Request();

        if(!$pimpleContainer) {
            $pimpleContainer = new Container(new PimpleContainer());
        }

        self::$container = $pimpleContainer;

        $callableResolver = new CallableResolver($pimpleContainer);

        if (strlen($str) > 1) {
            $str = rtrim($str, '/\\');
            $pos = strpos($str, '?');
           if ($pos !== false) {
                $url = substr($str, 0, $pos);
                $queryString = substr($str, $pos + 1);
                $request->parseQueryParams($queryString);
            } else {
                $url = $str;
            }
        } else {
            $url = $str;
        }

        // default Middlewares
        while($mid = array_shift(self::$defaultMiddlewares)) {
            $callable = $callableResolver->resolve($mid);
            call_user_func($callable, $request);
        }

        foreach (self::$routes as $pattern => $obj) {
            if (preg_match($pattern, $url, $params)) {
                array_shift($params);
                $request->parseUrlParams($params);
                
                while($mid = array_shift($obj['middlewares'])) {
                    $callable = $callableResolver->resolve($mid);
                    call_user_func($callable, $request);
                }

                try {
                    $callable = $callableResolver->resolve($obj['callback']);
                    call_user_func($callable, $request);
                } catch(\Throwable $err) {
                    $fbck = self::$errorFallback;
                    if($fbck) {
                        $callable = $callableResolver->resolve($fbck);
                        call_user_func($callable, $request, $err);
            		}
                }
                return;
            }
        }

        $fbck = self::$notFoundFallback;
		if($fbck) {
            $callable = $callableResolver->resolve($fbck);
            call_user_func($callable, $request);
		}
    }

    private static function resolve($fn)
    {
        if(is_callable($fn)) {
            return $fn;
        }

        if(!is_string($fn)) {
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
            if(empty($route['middlewares'])) {
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
