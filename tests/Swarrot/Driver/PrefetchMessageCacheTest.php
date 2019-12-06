<?php

namespace Swarrot\Tests\Driver;

use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;
use Swarrot\Driver\MessageCacheInterface;
use Swarrot\Driver\PrefetchMessageCache;

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
    public function setUp(): void
    {
        $this->driver = new PrefetchMessageCache();
    }

    /**
     * Test instance.
     */
    public function testInstance()
    {
        $this->assertInstanceOf(MessageCacheInterface::class, $this->driver);
    }

    /**
     * Test with one element.
     */
    public function testPushPop()
    {
        $message = $this->prophesize(Message::class);

        $this->driver->push('foo', $message->reveal());

        $this->assertSame($message->reveal(), $this->driver->pop('foo'));
    }

    /**
     * Test with multiple element.
     */
    public function testMultiplePushPop()
    {
        $message1 = $this->prophesize(Message::class);
        $message2 = $this->prophesize(Message::class);
        $message3 = $this->prophesize(Message::class);

        $this->driver->push('foo', $message1->reveal());
        $this->driver->push('foo', $message2->reveal());
        $this->driver->push('foo', $message3->reveal());

        $this->assertSame($message1->reveal(), $this->driver->pop('foo'));
        $this->assertSame($message2->reveal(), $this->driver->pop('foo'));
        $this->assertSame($message3->reveal(), $this->driver->pop('foo'));
    }
}
