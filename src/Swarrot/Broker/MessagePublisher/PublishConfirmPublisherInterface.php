<?php
declare(strict_types=1);

namespace Swarrot\Broker\MessagePublisher;

/**
 * Interface PublishConfirmPublisherInterface
 *
 * @package Swarrot\Broker\MessagePublisher
 */
interface PublishConfirmPublisherInterface
{
    /**
     * Enter confirm mode if not already in confirm mode. Only possible for non transactional channels.
     * Once in confirm mode you can't switch to transactional mode
     *
     * See https://www.rabbitmq.com/confirms.html
     *
     * Additional calls will update $timeout
     *
     * @param int|float $timeout (seconds) How long to wait for confirmations after publishing
     */
    public function enterConfirmMode($timeout = 0);
}
