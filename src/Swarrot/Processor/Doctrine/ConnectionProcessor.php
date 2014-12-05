<?php

namespace Swarrot\Processor\Doctrine;

use Doctrine\Common\Persistence\ConnectionRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Connections\MasterSlaveConnection;
use Doctrine\DBAL\DBALException;
use Swarrot\Broker\Message;
use Swarrot\Processor\ConfigurableInterface;
use Swarrot\Processor\ProcessorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class ConnectionProcessor implements ConfigurableInterface
{
    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var Connection[]
     */
    private $connections;

    public function __construct(ProcessorInterface $processor, $connections)
    {
        if ($connections instanceof ConnectionRegistry) {
            $connections = $connections->getConnections();
        }

        if (!is_array($connections)) {
            $connections = array($connections);
        }

        foreach ($connections as $connection) {
            if (!$connection instanceof Connection) {
                throw new \InvalidArgumentException(
                    sprintf(
                        '$connections must be an array of Connection, but one of the elements was %s',
                        is_object($connection) ? get_class($connection) : gettype($connection)
                    )
                );
            }
        }

        $this->processor = $processor;
        $this->connections = $connections;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, array $options)
    {
        if ($options['doctrine_ping']) {
            foreach ($this->connections as $connection) {
                if ($connection->isConnected()) {
                    try {
                        $connection->query($connection->getDatabasePlatform()->getDummySelectSQL());
                    } catch (DBALException $e) {
                        $connection->close(); // close timed out connections so that using them connects again
                    }
                }
            }
        }

        $result = $this->processor->process($message, $options);

        if ($options['doctrine_close_master']) {
            foreach ($this->connections as $connection) {
                if ($connection instanceof MasterSlaveConnection
                    && $connection->isConnectedToMaster()
                ) {
                    $connection->close();
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'doctrine_ping' => true,
            'doctrine_close_master' => true,
        ));
    }
}
