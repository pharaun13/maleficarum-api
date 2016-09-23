<?php
/**
 * Tests for the \Maleficarum\Api\Handler\Error\Generic class.
 */

namespace Maleficarum\Api\Test\Handler\Error;

class GenericTest extends \Maleficarum\Api\Test\ApiTestCase {
	/**
	 * TESTS
	 */

	/**  METHOD: \Maleficarum\Api\Handler\Error\Generic::handle() */
	
	/**
	 * @expectedException \RuntimeException
	 */
	public function testHandleCall() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Handler\Error\Generic')->handle('','','','',0);
	}
}