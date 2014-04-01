<?php

namespace Elixir\Filter;

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
