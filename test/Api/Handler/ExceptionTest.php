<?php
/**
 * Tests for the \Maleficarum\Api\Handler\Exception class.
 */

namespace Maleficarum\Api\Test\Handler;

class ExceptionTest extends \Maleficarum\Api\Test\ApiTestCase {
	/**
	 * ATTRIBUTES
	 */
	
	private static $exceptionInstance = [];
	
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
		
		// set up expected exception instance
		self::$exceptionInstance = [
			'Runtime' => new \RuntimeException('test message'),
		    'Generic' => new \GenericException('test message')
        ];
	}

	/**
	 * HELPERS
	 */
	
	public function getExceptionInstance($instance) {
		return isset(self::$exceptionInstance[$instance]) ? self::$exceptionInstance[$instance] : null;
	}
	
	/**
	 * TESTS
	 */

	/**  METHOD: \Maleficarum\Api\Handler\Exception::handle() */
	
	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testHandleIncorrect() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Handler\Exception')->handle();
	}

	public function testHandleCorrectGenericHandlerCall() {
		$this->assertNull(\Maleficarum\Ioc\Container::get('Maleficarum\Api\Handler\Exception')->handle($this->getExceptionInstance('Runtime')));
	}

	public function testHandleCorrectSpecificHandlerCall() {
		$this->assertNull(\Maleficarum\Ioc\Container::get('Maleficarum\Api\Handler\Exception')->handle($this->getExceptionInstance('Generic')));
	}
}