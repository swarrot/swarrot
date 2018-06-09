<?php

namespace Swarrot\Processor\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Swarrot\Broker\Message;
use Swarrot\Processor\ProcessorInterface;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class ObjectManagerProcessor implements ProcessorInterface
{
    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ProcessorInterface $processor, ManagerRegistry $managerRegistry)
    {
        $this->processor = $processor;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
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
