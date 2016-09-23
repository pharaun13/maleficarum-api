<?php
/**
 * Tests for the \Maleficarum\Api\Config\AbstractConfig class.
 */

namespace Maleficarum\Api\Test\Config;

class AbstractConfigTest extends \Maleficarum\Api\Test\ApiTestCase {
	/**
	 * ATTRIBUTES
	 */
	
	private $configMock = null;

	/**
	 * FIXTURES
	 */
	
	public function setUp() {
		// execute parent functionality
		parent::setUp();
		
		// setup a mock config object for each test
		$this->configMock = $this
			->getMockBuilder('Maleficarum\Api\Config\AbstractConfig')
			->disableOriginalConstructor()
			->setMethods(['load'])
			->getMockForAbstractClass();
		
		$configData = new \ReflectionProperty($this->configMock, 'data');
		$configData->setAccessible(true);
		$configData->setValue($this->configMock, ['testKey' => 'testValue']);
		$configData->setAccessible(false);
	}

	/**
	 * TESTS
	 */
	
	/**  METHOD: \Maleficarum\Api\Config\AbstractConfig::__construct */
	
	public function testConstructWithId() {
		$this->configMock
			->expects($this->once())
			->method('load')
			->with($this->equalTo('config_id'));
		
		$reflection = new \ReflectionClass('Maleficarum\Api\Config\AbstractConfig');
		$constructor = $reflection->getConstructor();
		$constructor->invoke($this->configMock, 'config_id');
	}
	
	/** METHOD: \Maleficarum\Api\Config\AbstractConfig::offsetExists() */
	
	public function testOffsetexistsSuccess() {
		$this->assertTrue($this->configMock->offsetExists('testKey'));
	}

	public function testOffsetexistsFailure() {
		$this->assertFalse($this->configMock->offsetExists(uniqid()));
	}

	/** METHOD: \Maleficarum\Api\Config\AbstractConfig::offsetUnset() */

	/**
	 * @expectedException \RuntimeException
	 */
	public function testOffsetunsetException() {
		$this->configMock->offsetUnset(uniqid());
	}

	/** METHOD: \Maleficarum\Api\Config\AbstractConfig::offsetGet() */

	public function testOffsetGetExisting() {
		$this->assertSame('testValue', $this->configMock->offsetGet('testKey'));
	}

	public function testOffsetGetNonExisting() {
		$this->assertNull($this->configMock->offsetGet(uniqid()));
	}
	
	/** METHOD: \Maleficarum\Api\Config\AbstractConfig::offsetSet() */

	/**
	 * @expectedException \RuntimeException
	 */
	public function testOffsetsetException() {
		$this->configMock->offsetSet(uniqid(), uniqid());
	}
}
