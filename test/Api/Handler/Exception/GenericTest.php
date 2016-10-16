<?php
/**
 * Tests for the \Maleficarum\Api\Handler\Exception\Generic class.
 */

namespace Maleficarum\Api\Test\Handler\Exception;

use Doctrine\Instantiator\Exception\InvalidArgumentException;

class GenericTest extends \Maleficarum\Api\Test\ApiTestCase {
	/**
	 * TESTS
	 */

	/**  METHOD: \Maleficarum\Api\Handler\Exception\Generic::handle() */

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testHandleIncorrect() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Handler\Exception\Generic')->handle(\Maleficarum\Ioc\Container::get('Exception'), null);
	}
	
	public function testHandleCorrectWithoutResponse() {
		// set up the tested exception
		$exception = \Maleficarum\Ioc\Container::get('Exception');
		
		$handler = $this->getMockBuilder('Maleficarum\Api\Handler\Exception\Generic')->setMethods(['handleGeneric'])->getMock();
		$handler
			->expects($this->once())
			->method('handleGeneric')
			->with(
				$this->equalTo($exception),
				$this->anything()
			);
		$handler->handle($exception, 0);
	}
	
	public function testHandleCorrectWithResponse() {
		// set up the tested exception
		$exception = \Maleficarum\Ioc\Container::get('Exception');

		// set up a response mock
		$resp = $this->getMockBuilder('Maleficarum\Response\Response')->disableOriginalConstructor()->setMethods(['setStatusCode'])->getMock();
		$resp
			->expects($this->once())
			->method('setStatusCode')
			->with(
				$this->equalTo(500)
			);
		
		$handler = $this->getMockBuilder('Maleficarum\Api\Handler\Exception\Generic')->setMethods(['handleJSON'])->getMock();
		$handler
			->setResponse($resp)
			->expects($this->once())
			->method('handleJSON')
			->with(
				$this->equalTo($exception),
				$this->anything()
			);
		$handler->handle($exception, 0);
	}
}