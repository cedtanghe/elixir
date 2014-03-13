<?php

namespace Elixir\Security\Authentification;

use Elixir\Dispatcher\Dispatcher;
use Elixir\Security\Authentification\Storage\StorageInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Manager extends Dispatcher
{
    /**
     * @var string
     */
    const DEFAULT_IDENTITY = 'default';
    
    /**
     * @var StorageInterface 
     */
    protected $_storage;
    
    /**
     * @var array
     */
    protected $_identities = array();

    /**
     * @param StorageInterface $pStorage
     */
    public function __construct(StorageInterface $pStorage) 
    {
        $this->_storage = $pStorage;
    }
    
    /**
     * @return StorageInterface 
     */
    public function getStorage()
    {
        return $this->_storage;
    }

    /**
     * @param AuthInterface $pAuth
     * @param string $pIdentity
     * @return Result
     */
    public function authenticate(AuthInterface $pAuth, $pIdentity = self::DEFAULT_IDENTITY)
    {
        $result = $pAuth->authenticate();
        
        if($result->getCode() == Result::SUCCESS)
        {
            $this->set($pIdentity, $result->getIdentity());
        }
        
        return $result;
    }
    
    /**
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->_storage->isEmpty();
    }

    /**
     * @param string $pKey
     * @return boolean
     */
    public function has($pKey)
    {
        return $this->_storage->has($pKey);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function get($pKey = self::DEFAULT_IDENTITY, $pDefault = null)
    {
        $identity = $this->_storage->get($pKey, $pDefault);
        
        if($identity instanceof Identity)
        {
            if(!$identity->hasListener(AuthEvent::IDENTITY_REMOVED))
            {
                $identity->addListener(AuthEvent::IDENTITY_REMOVED, array($this, 'onIdentityRemoved'));
            }
            
            if(!$identity->hasListener(AuthEvent::UPDATE))
            {
                $identity->addListener(AuthEvent::UPDATE, array($this, 'onIdentityUpdated'));
            }
        }
        
        return $identity;
    }
    
    /**
     * @param string $pKey
     * @param Identity $pValue
     */
    public function set($pKey, Identity $pIdentity)
    {
        $this->_storage->set($pKey, $pIdentity);
        $pIdentity->setDomain($pKey);
        $pIdentity->addListener(AuthEvent::IDENTITY_REMOVED, array($this, 'onIdentityRemoved'));
        $pIdentity->addListener(AuthEvent::UPDATE, array($this, 'onIdentityUpdated'));
        
        $this->_identities[$pKey] = $pIdentity;
    }
    
    /**
     * @internal
     * @param AuthEvent $e
     */
    public function onIdentityRemoved(AuthEvent $e)
    {
        $this->remove($e->getTarget()->getDomain());
    }
    
    /**
     * @internal
     * @param AuthEvent $e
     */
    public function onIdentityUpdated(AuthEvent $e)
    {
        $this->update($e->getTarget());
    }

    /**
     * @param string $pKey
     */
    public function remove($pKey)
    {
        $identity = $this->_storage->get($pKey);
        
        if($identity instanceof Identity)
        {
            $identity->removeListener(AuthEvent::IDENTITY_REMOVED, array($this, 'onIdentityRemoved'));
            $identity->removeListener(AuthEvent::UPDATE, array($this, 'onIdentityUpdated'));
        }
        
        $this->_storage->remove($pKey);
    }
    
    /**
     * @return array
     */
    public function gets()
    {
        return $this->_storage->gets();
    }
    
    /**
     * @param array $pData
     */
    public function sets(array $pData)
    {
        $identities = $this->_storage->gets();
        
        foreach($identities as $identity)
        {
            $identity->removeListener(AuthEvent::IDENTITY_REMOVED, array($this, 'onIdentityRemoved'));
            $identity->removeListener(AuthEvent::UPDATE, array($this, 'onIdentityUpdated'));
        }

        $this->_storage->sets(array());

        foreach($pData as $key => $value)
        {
            $this->set($key, $value);
        }
    }
    
    /**
     * @param Identity $pIdentity
     * @throws \RuntimeException
     */
    public function update(Identity $pIdentity)
    {
        $domain = $pIdentity->getDomain();
        
        if(null === $domain)
        {
            throw new \RuntimeException('No identity domain has been defined.');
        }
        
        $this->set($domain, $pIdentity);
    }
}