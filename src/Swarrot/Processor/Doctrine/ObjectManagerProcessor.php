<?php

namespace Swarrot\Processor\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Swarrot\Broker\Message;
use Swarrot\Processor\InitializableInterface;
use Swarrot\Processor\ProcessorInterface;
use Swarrot\Processor\DecoratorTrait;
use Swarrot\Processor\SleepyInterface;
use Swarrot\Processor\TerminableInterface;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class ObjectManagerProcessor implements ProcessorInterface, ConfigurableInterface, InitializableInterface, SleepyInterface, TerminableInterface
{
    use DecoratorTrait;

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
