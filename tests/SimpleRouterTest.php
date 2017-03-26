<?php

use Intec\Router\SimpleRouter;

class NachoTest extends PHPUnit_Framework_TestCase {

	public function testHasRoute()
	{
		$this->assertFalse(SimpleRouter::hasRoute('/empty'));

		SimpleRouter::add('/empty', function(){
			return 'empty';
		});

		$this->assertFalse(SimpleRouter::hasRoute('/empty'));
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

}
