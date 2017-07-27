# Simple Router for php >= 5.6.

[![Build Status](https://travis-ci.org/incluirtecnologia/SimpleRouter.svg?branch=master)](https://travis-ci.org/incluirtecnologia/SimpleRouter)

## How to Install:
`composer require 'intec/simple-router';`
## How to Use:

```php
use LibrasSAC\Router\SimpleRouter;

// add route '/hello'
SimpleRouter::add('/hello', function(){
	echo 'Hello!';
});

// match route hello
SimpleRouter::match('/hello');

// add route '/hello/<string>'
SimpleRouter::add('/hello/([a-zA-Z]*)', function($request){
	$name = request->getUrlParams()[0];
	echo "Hello $name!";
});

// match route hello/<string>. It Will print 'Hello Jorge!'
SimpleRouter::match('/hello/jorge');

// add multiple routes once
SimpleRouter::setRoutes([
	[
		'pattern' => '/my/name/is/([a-zA-Z]*)',
        	'middlewares' => [
		    function(request) {
			// middleware stuff
			// if you want to block the request at this point
			// you will need to use a redirect or exit.
			// Otherwise the router will call the next middleware
		    },
		    function($request) {
			// middleware 2
			// if you want to block the request at this point
			// you will need to use a redirect or exit
		    }
        ]
		'callback' => function($request) {
            $name = request->getUrlParams()[0];
			echo $name;
		}
	],
	[
		'pattern' => '/my/id/([0-9+])',
		'callback' => function($request) {
            $id = request->getUrlParams()[0];
			echo $id;
		}
	]
]);
```
