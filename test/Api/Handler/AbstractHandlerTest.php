<?php
/**
 * Tests for the \Maleficarum\Api\Handler\AbstractHandler class.
 */

namespace Maleficarum\Api\Test\Handler;

class AbstractHandlerTest extends \Maleficarum\Api\Test\ApiTestCase
{
    /**
     * FIXTURES
     */

    public function setUp() {
        // execute parent functionality
        parent::setUp();

        // reset debug level to 0
        $property = new \ReflectionProperty('Maleficarum\Api\Handler\AbstractHandler', 'debugLevel');
        $property->setAccessible(true);
        $property->setValue(0);
        $property->setAccessible(false);
    }

    /**
     * TESTS
     */

    /**  METHOD: \Maleficarum\Api\Handler\AbstractHandler::setDebugLevel() */

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetdebudlevelIncorrect() {
        \Maleficarum\Api\Handler\AbstractHandler::setDebugLevel(null);
    }

    public function testSetdebuglevelCorrect() {
        \Maleficarum\Api\Handler\AbstractHandler::setDebugLevel(15);

        $property = new \ReflectionProperty('Maleficarum\Api\Handler\AbstractHandler', 'debugLevel');
        $property->setAccessible(true);
        $this->assertSame(15, $property->getValue());
        $property->setAccessible(false);
    }
}
