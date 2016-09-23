<?php
/**
 * Tests for the \Maleficarum\Api\Config\Ini\Config class.
 */

namespace Maleficarum\Api\Test\Config\Ini;

class ConfigTest extends \Maleficarum\Api\Test\ApiTestCase {
	/**
	 * TESTS
	 */
	
	/**  METHOD: \Maleficarum\Api\Config\Ini\Config::load */

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigLoadIncorrectId() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Config\Ini\Config', [null]);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testConfigLoadUnreadableId() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Config\Ini\Config', [uniqid()]);
	}

	public function testConfigLoadCorrectId() {
		$cfg = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Config\Ini\Config', [CONFIG_PATH.DIRECTORY_SEPARATOR.'__example-config.ini']);
		$this->assertArrayHasKey('global', $cfg);
	}
	
	/**  STRUCTURE */

	public function testInheritance() {
		$cfg = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Config\Ini\Config', [CONFIG_PATH.DIRECTORY_SEPARATOR.'__example-config.ini']);
		$this->assertInstanceOf('Maleficarum\Api\Config\AbstractConfig', $cfg);
	}
}