<?php

namespace Elixir\View\Helper;

use Elixir\View\ViewInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface ContextInterface
{
    /**
     * @param ViewInterface $pValue
     */
    public function setView(ViewInterface $pValue);
}