<?php

namespace Intec\Router;

class SimpleRouter
{
    private static $routes = [];
    private function __construct()
    {

    }
    private function __clone()
    {

    }
    public static function add($pattern, callable $callback)
    {
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
        self::$routes[$pattern] = $callback;
    }
    public static function match($str)
    {
        if (strlen($str) > 1) {
            $str = rtrim($str, '/\\');
            $pos = strpos($str, '?');
           if ($pos !== false) {
                $url = substr($str, 0, $pos);
                $queryString = substr($str, $pos + 1);
                parse_str($queryString, $_GET);
            } else {
                $url = $str;
            }
        } else {
            $url = $str;
        }

        foreach (self::$routes as $pattern => $callback) {
            if (preg_match($pattern, $url, $params)) {

                array_shift($params);
                return call_user_func_array($callback, array_values($params));
            }
        }
    }
    public static function setRoutes($routes = [])
    {
        foreach ($routes as $route) {
            self::add($route['pattern'], $route['callback']);
        }
    }

	public static function hasRoute($pattern)
	{
		return in_array($pattern, self::$routes);
	}
}
