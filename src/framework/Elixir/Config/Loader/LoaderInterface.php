<?php

namespace Elixir\Config\Loader;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface LoaderInterface 
{
    /**
     * @param mixed $config
     * @param boolean $recursive
     * @return array
     */
    public function load($config, $recursive = false);
}
