<?php

namespace Elixir\HTTP\Session;

use Elixir\Dispatcher\DispatcherInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface SessionInterface extends DispatcherInterface
{
    /**
     * @var string
     */
    const FLASH_REDIRECT = 'redirect';
    
    /**
     * @var string
     */
    const FLASH_INFOS = 'infos';
    
    /**
     * @var string
     */
    const FLASH_SUCCESS = 'success';
    
    /**
     * @var string
     */
    const FLASH_ERROR = 'error';
    
    /**
     * @param \SessionHandlerInterface $pValue
     */
    public function setHandler(\SessionHandlerInterface $pValue);
    
    /**
     * @return \SessionHandlerInterface
     */
    public function geHandler();
    
    /**
     * @return boolean
     */
    public function exist();
    
    /**
     * @param string $pValue
     */
    public function setId($pValue);
    
    /**
     * @return string
     */
    public function getId();
    
    /**
     * @param string $pValue
     */
    public function setName($pValue);
    
    /**
     * @return string
     */
    public function getName();
    
    /**
     * @param boolean $pDeleteOldSession
     */
    public function regenerate($pDeleteOldSession = true);
    
    public function start();
    
    /**
     * @param mixed $pKey
     * @return boolean
     */
    public function has($pKey);
    
    /**
     * @param mixed $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function get($pKey, $pDefault = null);
    
    /**
     * @param mixed $pKey
     * @param mixed $pValue
     */
    public function set($pKey, $pValue);
    
    /**
     * @param mixed $pKey
     */
    public function remove($pKey);
    
    /**
     * @return array
     */
    public function gets();
    
    /**
     * @param array $pData
     */
    public function sets(array $pData);
    
    /**
     * @param mixed $pKey
     * @param mixed $pValue
     * @return mixed|void
     */
    public function flash($pKey = null, $pValue = null);
    
    public function clear();
    
    public function destroy();
}
