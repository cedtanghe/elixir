<?php

namespace Elixir\I18N\Loader;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface LoaderInterface 
{
    /**
     * @param mixed $pResource
     * @return array
     */
    public function load($pResource);
}
