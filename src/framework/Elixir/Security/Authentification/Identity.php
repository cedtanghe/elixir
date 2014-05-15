<?php

namespace Elixir\Security\Authentification;

use Elixir\Dispatcher\Dispatcher;
use Elixir\Security\Authentification\AuthEvent;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Identity extends Dispatcher
{
    /**
     * @var mixed
     */
    protected $_securityContext;
    
    /**
     * @var array 
     */
    protected $_data = [];
    
    /**
     * @var string 
     */
    protected $_domain;

    /**
     * @param array $pData
     * @param mixed $pSecurityContext
     */
    public function __construct(array $pData = [], $pSecurityContext = null)
    {
        $this->setData($pData);
        $this->_securityContext = $pSecurityContext;
    }
    
    /**
     * @param mixed $pValue
     */
    public function setSecurityContext($pValue)
    {
        $this->_securityContext = $pValue;
    }
    
    /**
     * @return mixed
     */
    public function getSecurityContext()
    {
        return $this->_securityContext;
    }
    
    /**
     * @param array $pValue
     */
    public function setData(array $pValue)
    {
        $this->_data = $pValue;
    }
    
    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }
    
    /**
     * @internal
     * @param string $pValue
     */
    public function setDomain($pValue)
    {
        $this->_domain = $pValue;
    }
    
    /**
     * @string type
     */
    public function getDomain()
    {
        return $this->_domain;
    }
    
    public function update()
    {
        $this->dispatch(new AuthEvent(AuthEvent::UPDATE));
    }
    
    public function remove()
    {
        $this->dispatch(new AuthEvent(AuthEvent::IDENTITY_REMOVED));
    }
    
    /**
     * @param string $pKey
     * @return mixed
     */
    public function __get($pKey)
    {
        return $this->_data[$pKey];
    }
    
    /**
     * @param string $pKey
     * @param mixed $pValue
     */
    public function __set($pKey, $pValue)
    {
        $this->_data[$pKey] = $pValue;
    }
}
