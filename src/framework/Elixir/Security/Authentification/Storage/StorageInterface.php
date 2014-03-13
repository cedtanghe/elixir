<?php

namespace Elixir\Security\Authentification\Storage;

use Elixir\Security\Authentification\Identity;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface StorageInterface
{
    /**
     * @return boolean
     */
    public function isEmpty();
    
    /**
     * @param string $pKey
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
     * @param Identity $pIdentity
     */
    public function set($pKey, Identity $pIdentity);
    
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
}