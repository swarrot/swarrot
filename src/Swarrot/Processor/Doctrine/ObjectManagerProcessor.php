<?php

namespace Swarrot\Processor\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Swarrot\Broker\MessageInterface;
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
    public function process(MessageInterface $message, array $options)
    {
        $result = $this->processor->process($message, $options);

        foreach ($this->managerRegistry->getManagers() as $managerName => $manager) {
            if (method_exists($manager, 'isOpen')
                && !$manager->isOpen()) {
                $this->managerRegistry->resetManager($managerName);

                continue;
            }

            $manager->clear();
        }

        return $result;
    }
}
