<?php

namespace Swarrot\Broker\MessageProvider;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Swarrot\Broker\Message;
use Swarrot\Driver\MessageCacheInterface;
use Swarrot\Driver\PrefetchMessageCache;
use Aws\Sqs\SqsClient;
use Guzzle\Service\Resource\Model;

/**
 * Class SqsMessageProviderTest.
 */
class SqsMessageProviderTest extends TestCase
{
    /**
     * @var SqsMessageProvider
     */
    protected $provider;

    protected $channel;
    protected $cache;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $this->channel = $this->prophesize(SqsClient::class);
        $this->cache = $this->prophesize(MessageCacheInterface::class);

        $this->provider = new SqsMessageProvider($this->channel->reveal(), 'foo', $this->cache->reveal());
    }

    /**
     * Test instance.
     */
    public function testInstance()
    {
        $this->assertInstanceOf(MessageProviderInterface::class, $this->provider);
    }

    /**
     * Test with no cache.
     */
    public function testGetWithNoCache()
    {
        $cache = new PrefetchMessageCache();

        $this->provider = new SqsMessageProvider($this->channel->reveal(), 'foo', $cache);

        $response = $this->prophesize(Model::class);
        $response->get(Argument::any())->willReturn([
            [
                'Body' => 'Body',
                'ReceiptHandle' => 'bar',
            ],
        ]);
        $this->channel->receiveMessage(Argument::any())->willReturn($response);

        $this->assertInstanceOf(Message::class, $this->provider->get());

        $this->channel->receiveMessage([
            'QueueUrl' => 'foo',
            'MaxNumberOfMessages' => 9,
            'WaitTimeSeconds' => 5,
        ])->shouldBeCalled();
    }

    /**
     * Test with no cache.
     */
    public function testGetWithNoResult()
    {
        $this->cache->pop(Argument::any())->willReturn(null);

        $response = $this->prophesize(Model::class);
        $response->get(Argument::any())->willReturn(null);
        $this->channel->receiveMessage(Argument::any())->willReturn($response);

        $this->assertNull($this->provider->get());

        $this->channel->receiveMessage([
            'QueueUrl' => 'foo',
            'MaxNumberOfMessages' => 9,
            'WaitTimeSeconds' => 5,
        ])->shouldBeCalled();
    }

    /**
     * Test with cache.
     */
    public function testGetWithCache()
    {
        $message = $this->prophesize(Message::class);

        $this->cache->pop(Argument::any())
            ->willReturn($message);

        $this->assertInstanceOf(Message::class, $this->provider->get());

        $this->cache->push(Argument::any())->shouldNotBeCalled();
        $this->channel->receiveMessage(Argument::any())->shouldNotBeCalled();
    }
}
