<?php

namespace Elixir\Security\Authentification\Storage;

use Elixir\HTTP\Session\SessionInterface;
use Elixir\Security\Authentification\Identity;
use Elixir\Security\Authentification\Storage\StorageInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Session implements StorageInterface
{
    /**
     * @var string
     */
    const STORAGE_KEY = '___AUTH_STORAGE___';
    
    /**
     * @var SessionInterface
     */
    protected $_session;
    
    /**
     * @param SessionInterface $pSession
     */
    public function __construct(SessionInterface $pSession) 
    {
        $this->_session = $pSession;
    }
    
    /**
     * @see StorageInterface::isEmpty()
     */
    public function isEmpty()
    {
        return count($this->_session->get(self::STORAGE_KEY, array())) == 0;
    }
    
    /**
     * @see StorageInterface::has()
     */
    public function has($pKey)
    {
        return $this->_session->has(array(self::STORAGE_KEY, $pKey));
    }
    
    /**
     * @see StorageInterface::get()
     */
    public function get($pKey, $pDefault = null)
    {
        $identity = $this->_session->get(array(self::STORAGE_KEY, $pKey), null);
        
        if(null !== $identity)
        {
            return $identity;
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @see StorageInterface::set()
     */
    public function set($pKey, Identity $pIdentity)
    {
        $this->_session->set(array(self::STORAGE_KEY, $pKey), $pIdentity);
    }
    
    /**
     * @see StorageInterface::remove()
     */
    public function remove($pKey)
    {
        $this->_session->remove(array(self::STORAGE_KEY, $pKey));
    }
    
    /**
     * @see StorageInterface::gets()
     */
    public function gets()
    {
        return $this->_session->get(self::STORAGE_KEY, array());
    }
    
    /**
     * @see StorageInterface::sets()
     * @throws \InvalidArgumentException
     */
    public function sets(array $pData)
    {
        foreach($pData as $key => $identity)
        {
            if(!$identity instanceof Identity)
            {
                throw new \InvalidArgumentException(sprintf('Key "%s" must be of type "Elixir\Security\Authentification\Identity".', $key));
            }
        }
        
        $this->_session->set(self::STORAGE_KEY, $pData);
    }
}
