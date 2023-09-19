<?php

namespace Swarrot\Processor\Doctrine;

use Doctrine\Persistence\ManagerRegistry;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 *
 * @final since 4.16.0
 */
class ObjectManagerProcessor implements ProcessorInterface
{
    private $processor;
    private $managerRegistry;

    public function __construct(ProcessorInterface $processor, ManagerRegistry $managerRegistry)
    {
        $this->processor = $processor;
        $this->managerRegistry = $managerRegistry;
    }

    public function process(Message $message, array $options): bool
    {
        try {
            return $this->processor->process($message, $options);
        } finally {
            foreach ($this->managerRegistry->getManagers() as $managerName => $manager) {
                if (method_exists($manager, 'isOpen')
                    && !$manager->isOpen()) {
                    $this->managerRegistry->resetManager($managerName);

                    continue;
                }

                $manager->clear();
            }
        }
    }
}
