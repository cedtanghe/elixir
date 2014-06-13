<?php

namespace Elixir\DI;

use Elixir\DI\ProviderInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface ContainerInterface
{
    /**
     * @var string
     */
    const SIMPLE = 'simple';
    
    /**
     * @var string
     */
    const SINGLETON = 'singleton';
    
    /**
     * @var string
     */
    const PROTECT = 'protect';
    
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
     * @param array $pArguments
     * @param mixed $pDefault
     * @return mixed 
     */
    public function get($pKey, array $pArguments = null, $pDefault = null);
    
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
     * @param array $pOptions
     */
    public function sets(array $pData, array $pOptions = []);

    /**
     * @param string $pAlias
     * @param string $pKey
     * @return boolean
     */
    public function hasAlias($pAlias, $pKey = null);
    
    /**
     * @param string $pKey
     * @param string $pAlias
     */
    public function addAlias($pKey, $pAlias);
    
    /**
     * @param string $pKey
     * @param string $pTag
     * @return boolean
     */
    public function hasTag($pKey, $pTag);
    
    /**
     * @param string $pKey
     * @param string $pTag
     */
    public function addTag($pKey, $pTag);
    
    /**
     * @param string $pKey
     * @param callable $pValue
     */
    public function extend($pKey, $pValue);
    
    /**
     * @param string $pKey
     * @param boolean $pWithConfiguration
     * @return mixed 
     */
    public function raw($pKey, $pWithConfiguration = false);
    
    /**
     * @param string $pKey
     * @return string
     */
    public function getStorageType($pKey);

    /**
     * @param ProviderInterface $pProvider
     */
    public function load(ProviderInterface $pProvider);
    
    /**
     * @param array|ContainerInterface $pData 
     */
    public function merge($pData);
}