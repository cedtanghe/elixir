<?php

namespace Elixir\Filter;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface FilterInterface
{
    /**
     * @param mixed $pContent
     * @param array $pOptions 
     * @return mixed
     */
    public function filter($pContent, array $pOptions = array());
}