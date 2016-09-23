<?php
/**
 * Tests for the \Maleficarum\Api\Error\Generic\Container class.
 */

namespace Maleficarum\Api\Test\Error\Generic;

class ContainerTest extends \Maleficarum\Api\Test\ApiTestCase {
	/**
	 * ATTRIBUTES
	 */

	private static $errorStructure = ['__default_error_namespace__' => []];
	
	/**
	 * FIXTURES
	 */

	public static function setUpBeforeClass() {
		// execute parent functionality
		parent::setUpBeforeClass();
		
		// setup expected error structure to use within this test suite
		$error = new \StdClass;
		$error->msg = 'msg';
		$error->code = '0000-000000';
		
		self::$errorStructure['__default_error_namespace__'][$error->code] = $error;
		self::$errorStructure['context_1'][$error->code] = $error;
	}

	/**
	 * PROVIDERS
	 */
	
	public function provideIncorrectErrors() {
		return [
			[null, null, null, null],
		    ['msg', null, null, null],
		    ['msg', '0000-000000', null, null],
		    ['msg', '0000-000000', 'context_1', null]
		];
	}
	
	/**
	 * TESTS
	 */

	/**  METHOD: \Maleficarum\Api\Error\Generic\Container::setErrors() */
	
	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSeterrorsIncorrect() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container')->setErrors([]);
	}
	
	public function testSeterrorsCorrect() {
		$container = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container')->setErrors(self::$errorStructure);
		$errors = new \ReflectionProperty(get_class($container), 'errors');
		$errors->setAccessible(true);
		$errors = $errors->getValue($container);
		$this->assertArrayHasKey('context_1', $errors);
	}

	/**  METHOD: \Maleficarum\Api\Error\Generic\Container::hasErrors() */

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testHaserrorsIncorrect() {
		$container = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container')->setErrors(self::$errorStructure);
		$container->hasErrors(null);
	}
	
	public function testHaserrorsCorrectTrue() {
		$container = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container')->setErrors(self::$errorStructure);
		$this->assertTrue($container->hasErrors());
		$this->assertTrue($container->hasErrors('context_1'));
	}
	
	public function testHaserrorsCorrectFalse() {
		$container = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container');
		$this->assertFalse($container->hasErrors());
		
        $container->setErrors(self::$errorStructure);
		$this->assertFalse($container->hasErrors(uniqid()));
	}

	/**  METHOD: \Maleficarum\Api\Error\Generic\Container::clearErrors() */
	
	public function testClearerrors() {
		$container = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container')->setErrors(self::$errorStructure);
		$container->clearErrors();
		$this->assertFalse($container->hasErrors());
	}

	/**  METHOD: \Maleficarum\Api\Error\Generic\Container::getErrors() */

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGeterrorsIncorrect() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container')->setErrors(self::$errorStructure)->getErrors(null);
	}
	
	public function testGetErrorsEmpty() {
		$container = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container');
		$this->assertSame([], $container->getErrors());
		$container->setErrors(self::$errorStructure);
		$this->assertSame([], $container->getErrors(uniqid()));
	}
	
	public function testGetErrorsWithErrors() {
		$container = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container')->setErrors(self::$errorStructure);
		$this->assertCount(1, $container->getErrors());
		$this->assertCount(1, $container->getErrors('context_1'));
	}

	/**  METHOD: \Maleficarum\Api\Error\Generic\Container::getAllErrors() */
	
	public function testGetallerrorsEmpty() {
		$container = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container');
		$errors = $container->getAllErrors();
		
		$this->assertCount(1, $errors);
		$this->assertArrayHasKey('__default_error_namespace__', $errors);
		$this->assertTrue(is_array($errors['__default_error_namespace__']));
		$this->assertEmpty($errors['__default_error_namespace__']);
	}
	
	public function testGetallErrorsWithErrors() {
		$container = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container')->setErrors(self::$errorStructure);
		$errors = $container->getAllErrors();

		$this->assertCount(2, $errors);
		$this->assertArrayHasKey('__default_error_namespace__', $errors);
		$this->assertArrayHasKey('context_1', $errors);
		$this->assertTrue(is_array($errors['__default_error_namespace__']));
		$this->assertTrue(is_array($errors['context_1']));
		$this->assertCount(1, $errors['__default_error_namespace__']);
		$this->assertCount(1, $errors['context_1']);
	}

	/**  METHOD: \Maleficarum\Api\Error\Generic\Container::addError() */

	/**
	 * @dataProvider provideIncorrectErrors
	 * @expectedException \InvalidArgumentException
	 */
	public function testAdderrorIncorrect($msg, $code, $ns, $duplicate) {
		$container = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container');
		$container->addError($code, $msg, $ns, $duplicate);
	}
	
	public function testAdderrorWithoutDuplication() {
		$container = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container')->addError('0000-000000', 'msg', 'context_1', false);
		$this->assertFalse($container->hasErrors());
		$this->assertTrue($container->hasErrors('context_1'));
	}
	
	public function testAdderrorWithDuplication() {
		$container = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container')->addError('0000-000000', 'msg', 'context_1', true);
		$this->assertTrue($container->hasErrors());
		$this->assertTrue($container->hasErrors('context_1'));
	}

	/**  METHOD: \Maleficarum\Api\Error\Generic\Container::mergeErrors() */

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testMergeerrorsIncorrect() {
		$container = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container')->setErrors(self::$errorStructure);
		$container->mergeErrors([]);
	}
	
	public function testMergeerrorsCorrect() {
		$container1 = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container')->setErrors(self::$errorStructure);
		$container2 = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Error\Generic\Container')->setErrors(self::$errorStructure)->addError('1111-111111', 'msg', 'context_2', true);
		
		$container1->mergeErrors($container2->getAllErrors());
		
		$errors = $container1->getAllErrors();
		$this->assertCount(3, $errors);
		$this->assertArrayHasKey('__default_error_namespace__', $errors);
		$this->assertArrayHasKey('context_1', $errors);
		$this->assertArrayHasKey('context_2', $errors);
		$this->assertTrue(is_array($errors['__default_error_namespace__']));
		$this->assertTrue(is_array($errors['context_1']));
		$this->assertTrue(is_array($errors['context_2']));
		$this->assertCount(2, $errors['__default_error_namespace__']);
		$this->assertCount(1, $errors['context_1']);
		$this->assertCount(1, $errors['context_2']);
	}
}
