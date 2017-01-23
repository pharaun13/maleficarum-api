<?php
/**
 * Tests for the \Maleficarum\Api\Rabbitmq\Connection class.
 */

namespace Maleficarum\Api\Test\Rabbitmq;

class ConnectionTest extends \Maleficarum\Api\Test\ApiTestCase
{
    /**
     * TESTS
     */

    /**  METHOD: \Maleficarum\Api\Rabbitmq\Connection::init() */

    public function testInit() {
        $con = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Rabbitmq\Connection')->init();

        $method = new \ReflectionMethod($con, 'getConnection');
        $method->setAccessible(true);
        $this->assertInstanceOf('PhpAmqpLib\Connection\AMQPConnection', $method->invoke($con));
        $method->setAccessible(false);
    }

    /**  METHOD: \Maleficarum\Api\Rabbitmq\Connection::__destruct() */

    public function testDestruct() {
        $con = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Rabbitmq\Connection')->init();
        unset($con);
    }

    /**  METHOD: \Maleficarum\Api\Rabbitmq\Connection::addCommand() */

    public function testAddcommand() {
        \Maleficarum\Ioc\Container::get('Maleficarum\Api\Rabbitmq\Connection')->addCommand($this->createMock('Maleficarum\Api\Worker\Command\AbstractCommand'));
    }

    /**  METHOD: \Maleficarum\Api\Rabbitmq\Connection::addCommands() */

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddcommandsEmpty() {
        \Maleficarum\Ioc\Container::get('Maleficarum\Api\Rabbitmq\Connection')->addCommands([]);
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddcommandsIncorrect() {
        \Maleficarum\Ioc\Container::get('Maleficarum\Api\Rabbitmq\Connection')->addCommands([null, null]);
    }

    public function testAddcommandsCorrect() {
        \Maleficarum\Ioc\Container::get('Maleficarum\Api\Rabbitmq\Connection')->addCommands([
            $this->createMock('Maleficarum\Api\Worker\Command\AbstractCommand'),
            $this->createMock('Maleficarum\Api\Worker\Command\AbstractCommand')
        ]);
    }
}
