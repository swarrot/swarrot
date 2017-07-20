<?php

namespace Swarrot\Broker\MessageProvider;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Swarrot\Driver\PrefetchMessageCache;

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
        $this->channel = $this->prophesize('Aws\Sqs\SqsClient');
        $this->cache = $this->prophesize('Swarrot\Driver\MessageCacheInterface');

        $this->provider = new SqsMessageProvider($this->channel->reveal(), 'foo', $this->cache->reveal());
    }

    /**
     * Test instance.
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Swarrot\Broker\MessageProvider\MessageProviderInterface', $this->provider);
    }

    /**
     * Test with no cache.
     */
    public function testGetWithNoCache()
    {
        $cache = new PrefetchMessageCache();

        $this->provider = new SqsMessageProvider($this->channel->reveal(), 'foo', $cache);

        $response = $this->prophesize('Guzzle\Service\Resource\Model');
        $response->get(Argument::any())->willReturn([
            [
                'Body' => 'Body',
                'ReceiptHandle' => 'bar',
            ],
        ]);
        $this->channel->receiveMessage(Argument::any())->willReturn($response);

        $this->assertInstanceOf('Swarrot\Broker\Message', $this->provider->get());

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

        $response = $this->prophesize('Guzzle\Service\Resource\Model');
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
        $message = $this->prophesize('Swarrot\Broker\Message');

        $this->cache->pop(Argument::any())
            ->willReturn($message);

        $this->assertInstanceOf('Swarrot\Broker\Message', $this->provider->get());

        $this->cache->push(Argument::any())->shouldNotBeCalled();
        $this->channel->receiveMessage(Argument::any())->shouldNotBeCalled();
    }
}
