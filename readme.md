Simple Router for php >= 5.5.

How to Install:
	composer require 'intec/simple-router';
How to Use:

```php
use LibrasSAC\Router\SimpleRouter;

// add route '/hello'
SimpleRouter::add('/hello', function(){
	echo 'Hello!';
});

// match route hello
SimpleRouter::match('/hello');

// add route '/hello/<string>'
SimpleRouter::add('/hello/([a-zA-Z]*)', function($name){
	echo "Hello $name!";
});

// match route hello/<string>. It Will print 'Hello Jorge!'
SimpleRouter::match('/hello/jorge');

// add multiple routes once
SimpleRouter::setRoutes([
	[
		'pattern' => '/my/name/is/([a-zA-Z]*)',
		'callback' => function($name) {
			echo $name;
		}
	],
	[
		'pattern' => '/my/id/([0-9+])',
		'callback' => function($id) {
			echo $id;
		}
	]
]);
```
