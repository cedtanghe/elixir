<?php

namespace Elixir\DI;

use Elixir\DI\ContainerInterface;
use Elixir\DI\ProviderInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface ContainerInterface
{
    /**
     * @var string
     */
    const BIND = 'bind';
    
    /**
     * @var string
     */
    const SINGLETON = 'singleton';
    
    /**
     * @var string
     */
    const WRAP = 'wrap';
    
    /**
     * @var string
     */
    const EXTEND = 'extend';
    
    /**
     * @var string
     */
    const UNLOCKED = 'unlocked';
    
    /**
     * @var string
     */
    const READ_ONLY = 'read_only';
    
    /**
     * @var string
     */
    const IGNORE_IF_ALREADY_EXISTS = 'ignore_if_already_exists';
    
    /**
     * @var string
     */
    const THROW_IF_ALREADY_EXISTS = 'throw_if_already_exists';
    
    /**
     * @param string $pValue
     */
    public function setLockMode($pValue);
    
    /**
     * @return string
     */
    public function getLockMode();
    
    /**
     * @param string $pKey
     * @return boolean 
     */
    public function has($pKey);
    
    /**
     * @param string $pKey
     * @param array $pOptions
     * @param mixed $pDefault
     * @return mixed 
     */
    public function get($pKey, array $pOptions = [], $pDefault = null);
    
    /**
     * @param string $pKey
     * @param mixed $pValue 
     * @param array $pOptions 
     */
    public function set($pKey, $pValue, array $pOptions = []);
    
    /**
     * @param string $pKey 
     */
    public function remove($pKey);
    
    /**
     * @param array $pOptions
     * @return array 
     */
    public function gets(array $pOptions = []);
    
    /**
     * @param array $pData
     * @param array $pGlobalOptions
     */
    public function sets(array $pData, array $pGlobalOptions = []);
    
    /**
     * @param string $pKey
     * @param string $pAlias
     */
    public function addAlias($pKey, $pAlias);
    
    /**
     * @param string $pKey
     * @param string $pTag
     */
    public function addTag($pKey, $pTag);
    
    /**
     * @param string $pTag
     * @param array $pArguments
     * @param mixed $pDefault
     * @return array|mixed
     */
    public function findByTag($pTag, array $pArguments = [], $pDefault = null);
    
    /**
     * @param string $pKey
     * @return array 
     */
    public function raw($pKey);

    /**
     * @param ProviderInterface $pProvider
     */
    public function addProvider(ProviderInterface $pProvider);
    
    /**
     * @param array|ContainerInterface $pData 
     */
    public function merge($pData);
}
