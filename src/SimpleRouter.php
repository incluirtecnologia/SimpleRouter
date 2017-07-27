<?php

namespace Intec\Router;

class SimpleRouter
{
    private static $routes = [];
    const ROUTE_NOT_FOUND = '/404';

    private function __construct()
    {

    }
    private function __clone()
    {

    }

	private static function buildPattern($pattern)
	{
		return '/^' . str_replace('/', '\/', $pattern) . '$/';
	}

    public static function add($pattern, callable $callback, array $middlewares = [])
    {
        $pattern = self::buildPattern($pattern);
        self::$routes[$pattern] = [
            'callback' => $callback,
            'middlewares' => $middlewares,
        ];
    }

    public static function match($str)
    {
        $request = new Request();

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

        foreach (self::$routes as $pattern => $obj) {
            if (preg_match($pattern, $url, $params)) {
                array_shift($params);
                $request->parseUrlParams($params);
                while($mid = array_shift($obj['middlewares'])) {
                    $mid($request);
                }
                $obj['callback']($request);
                return;
            }
        }
        if($request->isXmlHttpRequest()) {
            http_response_code(404);
            exit;
        } else {
            self::redirectTo(self::ROUTE_NOT_FOUND);
        }
    }

    public static function setRoutes($routes = [])
    {
		self::clear();

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
}
