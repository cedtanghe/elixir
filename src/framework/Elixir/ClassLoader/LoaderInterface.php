<?php

namespace Elixir\ClassLoader;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface LoaderInterface 
{
    /**
     * @var string
     */
    const NAMESPACE_SEPARATOR = '\\';
    
    /**
     * @var string
     */
    const PREFIX_SEPARATOR = '_';
    
    public function register();
    public function unregister();
    
    /**
     * @param string $pClassName
     * @return boolean 
     */
    public function classExist($pClassName);
    
    /**
     * @param string $pClassName
     * @return boolean
     */
    public function loadClass($pClassName);
}
