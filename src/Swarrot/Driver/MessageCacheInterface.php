<?php

namespace Swarrot\Driver;

use Swarrot\Broker\MessageInterface;

/**
 * Interface MessageCacheInterface.
 */
interface MessageCacheInterface
{
    /**
     * Pushes a message to the end of the cache.
     *
     * @param string  $queueName
     * @param MessageInterface $message
     */
    public function push($queueName, MessageInterface $message);

    /**
     * Get the next message in line. Or nothing if there is no more
     * in the cache.
     *
     * @param string $queueName
     *
     * @return MessageInterface|null
     */
    public function pop($queueName);
}
