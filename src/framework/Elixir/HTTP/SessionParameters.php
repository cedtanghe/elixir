<?php

namespace Elixir\HTTP;

use Elixir\HTTP\ParametersInterface;
use Elixir\HTTP\Session\SessionInterface;
use Elixir\Util\Arr;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class SessionParameters implements ParametersInterface
{
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
     * @return SessionInterface
     */
    public function getSession()
    {
        return $this->_session;
    }

    /**
     * @see ParametersInterface::has()
     */
    public function has($pKey)
    {
        return $this->_session->has($pKey);
    }
    
    /**
     * @see ParametersInterface::get()
     */
    public function get($pKey, $pDefault = null, $pSanitize = null)
    {
        return $this->_session->get($pKey, $pDefault);
    }
    
    /**
     * @see ParametersInterface::set()
     */
    public function set($pKey, $pValue)
    {
        $this->_session->set($pKey, $pValue);
    }
    
    /**
     * @see ParametersInterface::remove()
     */
    public function remove($pKey)
    {
        $this->_session->remove($pKey);
    }
    
    /**
     * @see ParametersInterface::gets()
     */
    public function gets($pSanitize = null)
    {
        return $this->_session->gets();
    }
    
    /**
     * @see ParametersInterface::sets()
     */
    public function sets(array $pData)
    {
        return $this->_session->sets($pData);
    }
    
    /**
     * @see ParametersInterface::merge()
     */
    public function merge($pData, $pRecursive = false)
    {
        if($pData instanceof self)
        {
            $pData = $this->gets();
        }
        
        $this->sets($pRecursive ? Arr::merge($this->gets(), $pData) : array_merge($$this->gets(), $pData));
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     * @return mixed|void
     */
    public function __call($pMethod, $pArguments)
    {
        return call_user_func_array(array($this->_session, $pMethod), $pArguments);
    }
}