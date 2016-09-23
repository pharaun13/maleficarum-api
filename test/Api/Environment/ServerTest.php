<?php
/**
 * Tests for the \Maleficarum\Api\Environment\Server class.
 */

namespace Maleficarum\Api\Test\Environment;

class ServerTest extends \Maleficarum\Api\Test\ApiTestCase {
	/**
	 * ATTRIBUTES
	 */

	private $envDataMock = [
		'APPLICATION_ENVIRONMENT' => 'local',
	    'testKey' => 'testValue'
	];
	
	/**
	 * TESTS
	 */
	
	/**  METHOD: \Maleficarum\Api\Environment\Server::getCurrentEnvironment() */

	/**
	 * @expectedException \RuntimeException
	 */
	public function testGetcurrentenvironmentFailure() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Environment\Server', [[]])->getCurrentEnvironment();
	}
	
	public function testGetcurrentenvironmentSuccess() {
		$this->assertSame('local', \Maleficarum\Ioc\Container::get('Maleficarum\Api\Environment\Server', [$this->envDataMock])->getCurrentEnvironment());
	}

	/** METHOD: \Maleficarum\Api\Environment\Server::offsetExists() */

	public function testOffsetexistsSuccess() {
		$this->assertTrue(\Maleficarum\Ioc\Container::get('Maleficarum\Api\Environment\Server', [$this->envDataMock])->offsetExists('testKey'));
	}

	public function testOffsetexistsFailure() {
		$this->assertFalse(\Maleficarum\Ioc\Container::get('Maleficarum\Api\Environment\Server', [$this->envDataMock])->offsetExists(uniqid()));
	}

	/** METHOD: \Maleficarum\Api\Environment\Server::offsetUnset() */

	/**
	 * @expectedException \RuntimeException
	 */
	public function testOffsetunsetException() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Environment\Server', [$this->envDataMock])->offsetUnset(uniqid());
	}

	/** METHOD: \Maleficarum\Api\Environment\Server::offsetGet() */

	public function testOffsetGetExisting() {
		$this->assertSame('testValue', \Maleficarum\Ioc\Container::get('Maleficarum\Api\Environment\Server', [$this->envDataMock])->offsetGet('testKey'));
	}

	public function testOffsetGetNonExisting() {
		$this->assertNull(\Maleficarum\Ioc\Container::get('Maleficarum\Api\Environment\Server', [$this->envDataMock])->offsetGet(uniqid()));
	}

	/** METHOD: \Maleficarum\Api\Environment\Server::offsetSet() */

	/**
	 * @expectedException \RuntimeException
	 */
	public function testOffsetsetException() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Environment\Server', [$this->envDataMock])->offsetSet(uniqid(), uniqid());
	}
}