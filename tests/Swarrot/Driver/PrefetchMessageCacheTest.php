<?php

namespace Swarrot\Driver;

use PHPUnit\Framework\TestCase;

/**
 * Class PrefetchMessageCacheTest.
 */
class PrefetchMessageCacheTest extends TestCase
{
    /**
     * @var PrefetchMessageCache
     */
    protected $driver;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $this->driver = new PrefetchMessageCache();
    }

    /**
     * Test instance.
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Swarrot\Driver\MessageCacheInterface', $this->driver);
    }

    /**
     * Test with one element.
     */
    public function testPushPop()
    {
        $message = $this->prophesize('Swarrot\Broker\Message');

        $this->driver->push('foo', $message->reveal());

        $this->assertSame($message->reveal(), $this->driver->pop('foo'));
    }

    /**
     * Test with multiple element.
     */
    public function testMultiplePushPop()
    {
        $message1 = $this->prophesize('Swarrot\Broker\Message');
        $message2 = $this->prophesize('Swarrot\Broker\Message');
        $message3 = $this->prophesize('Swarrot\Broker\Message');

        $this->driver->push('foo', $message1->reveal());
        $this->driver->push('foo', $message2->reveal());
        $this->driver->push('foo', $message3->reveal());

        $this->assertSame($message1->reveal(), $this->driver->pop('foo'));
        $this->assertSame($message2->reveal(), $this->driver->pop('foo'));
        $this->assertSame($message3->reveal(), $this->driver->pop('foo'));
    }
}
