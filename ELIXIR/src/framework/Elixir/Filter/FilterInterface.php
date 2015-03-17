<?php

namespace Elixir\Filter;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface FilterInterface
{
    /**
     * @param mixed $pContent
     * @param array $pOptions 
     * @return mixed
     */
    public function filter($pContent, array $pOptions = []);
}
