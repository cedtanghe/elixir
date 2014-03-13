<?php

namespace Elixir\View;

use Elixir\View\Storage\StorageInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface ViewInterface
{
    /**
     * @param string $pKey
     * @return boolean
     */
    public function has($pKey);
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function get($pKey, $pDefault = null);
    
    /**
     * @param string $pKey
     * @param mixed $pValue
     */
    public function set($pKey, $pValue);
    
    /**
     * @param string $pKey
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
     * @param string|StorageInterface $pTemplate
     * @param array $pData
     * @return string
     */
    public function render($pTemplate, array $pData = array());
}