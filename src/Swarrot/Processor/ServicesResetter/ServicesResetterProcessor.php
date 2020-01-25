<?php

declare(strict_types=1);

namespace Swarrot\Processor\ServicesResetter;

use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @author Pierrick Vignand <pierrick.vignand@gmail.com>
 */
class ServicesResetterProcessor implements ProcessorInterface
{
    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var ResetInterface
     */
    private $servicesResetter;

    public function __construct(ProcessorInterface $processor, ResetInterface $servicesResetter)
    {
        $this->processor = $processor;
        $this->servicesResetter = $servicesResetter;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
    {
        try {
            return $this->processor->process($message, $options);
        } finally {
            $this->servicesResetter->reset();
        }
    }
}
