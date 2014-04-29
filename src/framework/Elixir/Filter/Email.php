<?php

namespace Elixir\Filter;

use Elixir\Filter\FilterAbstract;
use Elixir\Filter\FilterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Email extends FilterAbstract
{
    /**
     * @see FilterInterface::filter()
     */
    public function filter($pContent, array $pOptions = array())
    {
        return filter_var($pContent, FILTER_SANITIZE_EMAIL);
    }
}
