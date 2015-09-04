<?php

namespace Elixir\Config\Loader;

use Elixir\Config\Loader\LoaderInterface;

abstract class LoaderAbstract implements LoaderInterface
{
    /**
     * @var string 
     */
    protected $environment;

    /**
     * @var boolean 
     */
    protected $strict;

    /**
     * @param string $environment
     * @param boolean $strict
     */
    public function __construct($environment = null, $strict = false)
    {
        $this->environment = $environment;
        $this->strict = $strict;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return boolean
     */
    public function isStrict() 
    {
        return $this->strict;
    }

    /**
     * @param mixed $data
     * @param boolean $recursive
     * @return array
     */
    abstract protected function parse($data, $recursive);
}
