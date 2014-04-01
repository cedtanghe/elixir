<?php

namespace Elixir\View;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface GlobalInterface
{
    /**
     * @param string $pKey
     * @param mixed $pValue
     */
    public function setGlobal($pKey, $pValue);
    
    /**
     * @param string $pKey
     */
    public function globalize($pKey);
    
    /**
     * @param string $pKey
     * @return boolean
     */
    public function isGlobal($pKey);
}