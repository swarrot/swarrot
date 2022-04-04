<?php

namespace Swarrot\Processor\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Connections\MasterSlaveConnection;
use Doctrine\DBAL\DBALException as DBAL2Exception;
use Doctrine\DBAL\Exception as DBAL3Exception;
use Doctrine\Persistence\ConnectionRegistry;
use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\ProcessorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class ConnectionProcessor implements ConfigurableInterface
{
    private $processor;
    /**
     * @var Connection[]
     */
    private $connections;

    /**
     * @param ConnectionRegistry|Connection[]|Connection $connections
     */
    public function __construct(ProcessorInterface $processor, $connections)
    {
        if ($connections instanceof ConnectionRegistry) {
            $connections = $connections->getConnections();
        } elseif ($connections instanceof Connection) {
            $connections = [$connections];
        } elseif (\is_array($connections)) {
            foreach ($connections as $connection) {
                if (!$connection instanceof Connection) {
                    throw new \InvalidArgumentException(sprintf('$connections must be an array of Connection, but one of the elements was %s', \is_object($connection) ? \get_class($connection) : \gettype($connection)));
                }
            }
        } else {
            throw new \InvalidArgumentException('$connections must be an array of Connection, a ConnectionRegistry or a single Connection.');
        }

        $this->processor = $processor;
        $this->connections = $connections;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options): bool
    {
        if ($options['doctrine_ping']) {
            foreach ($this->connections as $connection) {
                if ($connection->isConnected()) {
                    try {
                        $connection->query($connection->getDatabasePlatform()->getDummySelectSQL());
                    } catch (DBAL2Exception | DBAL3Exception $e) {
                        $connection->close(); // close timed out connections so that using them connects again
                    }
                }
            }
        }

        try {
            return $this->processor->process($message, $options);
        } finally {
            if ($options['doctrine_close_master']) {
                foreach ($this->connections as $connection) {
                    if ($connection instanceof MasterSlaveConnection
                        && $connection->isConnectedToMaster()
                    ) {
                        $connection->close();
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'doctrine_ping' => true,
            'doctrine_close_master' => true,
        ]);
    }
}
