<?php

namespace Elixir\Routing\Loader;

use Elixir\Routing\Collection;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface LoaderInterface 
{
    /**
     * @var string
     */
    const GLOBALS = '_globals';
    
    /**
     * @param mixed $pConfig
     * @param Collection $pCollection
     * @return Collection
     */
    public function load($pConfig, Collection $pCollection = null);
}
