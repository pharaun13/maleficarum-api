<?php

\Maleficarum\Ioc\Container::register('Maleficarum\Api\Handler\Exception\Generic', function () {
    // get current debug level
    $property = new \ReflectionProperty('Maleficarum\Api\Handler\AbstractHandler', 'debugLevel');
    $property->setAccessible(true);
    $debugLevel = $property->getValue();
    $property->setAccessible(false);

    // setup the mock
    $mock = $this->createMock('Maleficarum\Api\Handler\Exception\Generic');
    $mock
        ->expects($this->once())
        ->method('handle')
        ->with(
            $this->callback(function ($exc) {
                return $exc === $this->getExceptionInstance('Runtime') || $exc === $this->getExceptionInstance('Generic');
            }),
            $this->equalTo($debugLevel)
        );

    return $mock;
});
