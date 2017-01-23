<?php

\Maleficarum\Ioc\Container::register('PhpAmqpLib\Connection\AMQPConnection', function () {
    $connection = $this->getMockBuilder('PhpAmqpLib\Connection\AMQPConnection')->disableOriginalConstructor();

    // testDestruct
    if ($this->getContext() === 'testDestruct') {
        $connection = $connection
            ->setMethods(['close'])
            ->getMock();
        $connection
            ->expects($this->exactly(1))
            ->method('close');
    }

    // testAddcommand
    if ($this->getContext() === 'testAddcommand') {
        // sub mock
        $channelMock = $this->createMock('PhpAmqpLib\Channel\AMQPChannel');
        $channelMock
            ->expects($this->once())
            ->method('basic_publish')
            ->with($this->callback(function ($object) {
                return ($object instanceOf \PhpAmqpLib\Message\AMQPMessage);
            }));

        $channelMock
            ->expects($this->once())
            ->method('close');

        $connection = $connection->setMethods(['channel'])->getMock();
        $connection
            ->expects($this->exactly(1))
            ->method('channel')
            ->will($this->returnValue($channelMock));
    }

    // testAddcommandsCorrect
    if ($this->getContext() === 'testAddcommandsCorrect') {
        // sub mock
        $channelMock = $this->createMock('PhpAmqpLib\Channel\AMQPChannel');
        $channelMock
            ->expects($this->exactly(2))
            ->method('batch_basic_publish')
            ->with($this->callback(function ($object) {
                return ($object instanceOf \PhpAmqpLib\Message\AMQPMessage);
            }));

        $channelMock
            ->expects($this->once())
            ->method('publish_batch');

        $channelMock
            ->expects($this->once())
            ->method('close');

        $connection = $connection->setMethods(['channel'])->getMock();
        $connection
            ->expects($this->exactly(1))
            ->method('channel')
            ->will($this->returnValue($channelMock));
    }

    // fallback to default context behaviour
    if (!($connection instanceof \PhpAmqpLib\Connection\AMQPConnection)) $connection = $connection->getMock();

    return $connection;
});

\Maleficarum\Ioc\Container::register('PhpAmqpLib\Message\AMQPMessage', function () {
    return $this->createMock('PhpAmqpLib\Message\AMQPMessage');
});
