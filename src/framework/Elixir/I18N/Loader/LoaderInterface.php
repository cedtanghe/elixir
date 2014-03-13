<?php

namespace Elixir\I18N\Loader;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface LoaderInterface 
{
    /**
     * @param mixed $pResource
     * @return array
     */
    public function load($pResource);
}
