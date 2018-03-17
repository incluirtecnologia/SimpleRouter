<?php

use Intec\Router\SimpleRouter;

class SimpleRouterTest extends PHPUnit_Framework_TestCase {

	public function testHasRoute()
	{
		$this->assertFalse(SimpleRouter::hasRoute('/empty'));

		SimpleRouter::add('/empty', function(){
			return 'empty';
		});

		$this->assertTrue(SimpleRouter::hasRoute('/empty'));
	}

	public function testMatch()
	{
		SimpleRouter::add('/empty', function(){
			return 'empty';
		});

		SimpleRouter::add('/not-empty', function(){
			return 'not-e';
		});

		$this->assertEquals(SimpleRouter::match('/empty'), 'empty');
		$this->assertNotEquals(SimpleRouter::match('/not-empty'), 'not-empty');
	}

	public function testSetRoutes()
	{

		SimpleRouter::setRoutes([
			[
				'pattern' => '/route1',
				'callback' => function(){}
			],
			[
				'pattern' => '/route2',
				'callback' => function(){}
			],
			[
				'pattern' => '/route/([a-zA-Z]+[0-9])',
				'callback' => function(){}
			],
		]);

		$this->assertTrue(SimpleRouter::hasRoute('/route1'));
		$this->assertTrue(SimpleRouter::hasRoute('/route2'));
		$this->assertTrue(SimpleRouter::hasRoute('/route/([a-zA-Z]+[0-9])'));
		$this->assertFalse(SimpleRouter::hasRoute('/route/hi10'));
	}

	public function testClear()
	{
		SimpleRouter::add('/clear', function(){});
		$this->assertTrue(SimpleRouter::hasRoute('/clear'));
		SimpleRouter::clear();
		$this->assertFalse(SimpleRouter::hasRoute('/clear'));
	}
}
