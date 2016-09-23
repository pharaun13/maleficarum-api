<?php

	\Maleficarum\Ioc\Container::register('Maleficarum\Api\Handler\Error\Generic', function() {
		// get current debug level
		$property = new \ReflectionProperty('Maleficarum\Api\Handler\AbstractHandler', 'debugLevel');
		$property->setAccessible(true);
		$debugLevel = $property->getValue();
		$property->setAccessible(false);
		
		// setup the mock
		$mock = $this->createMock('Maleficarum\Api\Handler\Error\Generic');
		$mock
			->expects($this->once())
			->method('handle')
			->with(
				$this->equalTo('errno'),
				$this->equalTo('errstr'),
				$this->equalTo('errfile'),
				$this->equalTo('errline'),
				$this->equalTo($debugLevel)
			);
		
		return $mock;
	});