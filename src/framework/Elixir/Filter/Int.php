<?php

namespace Elixir\Filter;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Int extends FilterAbstract
{
    /**
     * @see FilterInterface::filter()
     */
    public function filter($pContent, array $pOptions = array())
    {
        return filter_var($pValue, FILTER_SANITIZE_NUMBER_INT);
    }
}