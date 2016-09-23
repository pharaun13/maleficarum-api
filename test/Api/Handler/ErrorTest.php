<?php
/**
 * Tests for the \Maleficarum\Api\Handler\Error class.
 */

namespace Maleficarum\Api\Test\Handler;

class ErrorTest extends \Maleficarum\Api\Test\ApiTestCase {
	/**
	 * FIXTURES
	 */

	public static function setUpBeforeClass() {
		// execute parent functionality
		parent::setUpBeforeClass();

		// reset debug level to 0
		$property = new \ReflectionProperty('Maleficarum\Api\Handler\AbstractHandler', 'debugLevel');
		$property->setAccessible(true);
		$property->setValue(0);
		$property->setAccessible(false);
	}
	
	/**
	 * TESTS
	 */

	/**  METHOD: \Maleficarum\Api\Handler\Error::handle() */
	
	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testHandleIncorrect() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Handler\Error')->handle();
	}
	
	public function testHandleCorrect() {
		$this->assertNull(\Maleficarum\Ioc\Container::get('Maleficarum\Api\Handler\Error')->handle('errno', 'errstr', 'errfile', 'errline', 'errcontext'));
	}
}