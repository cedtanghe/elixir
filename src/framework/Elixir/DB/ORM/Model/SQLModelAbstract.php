<?php

namespace Elixir\DB\ORM\Model;

use Elixir\DB\ORM\Entity;
use Elixir\DB\ORM\EntityEvent;
use Elixir\DB\ORM\RepositoryInterface;
use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
abstract class SQLModelAbstract extends Entity implements RepositoryInterface
{
    /**
     * @var string
     */
    const DEFAULT_CONNECTION_KEY = 'db.default';
    
    /**
     * @var ContainerInterface
     */
    protected $connectionManager;
    
    /**
     * @param ContainerInterface $manager
     * @param array $data
     */
    public function __construct(ContainerInterface $manager = null, array $data = [])
    {
        $this->setConnectionManager($manager);
        parent::__construct($data);
        
        $this->addListener(EntityEvent::CREATE_ENTITY, function(EntityEvent $e)
        {
            $entity = $e->getEntity();
            $e->setEntity(new $entity($this->connectionManager));
        });
    }
    
    /**
     * @see RepositoryInterface::setConnectionManager()
     */
    public function setConnectionManager(ContainerInterface $value)
    {
        $this->connectionManager = $value;
    }

    /**
     * @see RepositoryInterface::getConnectionManager()
     */
    public function getConnectionManager() 
    {
        return $this->connectionManager;
    }

    /**
     * @see RepositoryInterface::getConnection()
     */
    public function getConnection($key = null) 
    {
        if (null !== $key) 
        {
            if ($this->connectionManager->has($key))
            {
                return $this->connectionManager->get($key);
            }
        }

        return $this->connectionManager->get(self::DEFAULT_CONNECTION_KEY);
    }
}
