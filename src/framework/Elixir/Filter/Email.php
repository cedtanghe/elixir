<?php

namespace Elixir\Filter;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
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
