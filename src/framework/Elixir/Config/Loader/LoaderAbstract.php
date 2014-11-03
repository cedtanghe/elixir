<?php

namespace Elixir\Config\Loader;

use Elixir\Config\Loader\LoaderInterface;

abstract class LoaderAbstract implements LoaderInterface
{
    /**
     * @var string 
     */
    protected $_environment;
    
    /**
     * @var boolean 
     */
    protected $_strict;

    /**
     * @param string $pEnvironment
     * @param boolean $pStrict
     */
    public function __construct($pEnvironment = null, $pStrict = false) 
    {
        $this->_environment = $pEnvironment;
        $this->_strict = $pStrict;
    }
    
    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->_environment;
    }
    
    /**
     * @return boolean
     */
    public function isStrict()
    {
        return $this->_strict;
    }
    
    /**
     * @param mixed $pData
     * @param boolean $pRecursive
     */
    abstract protected function parse($pData, $pRecursive = false);
}

