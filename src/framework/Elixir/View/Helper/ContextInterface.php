<?php

namespace Elixir\View\Helper;

use Elixir\View\ViewInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface ContextInterface
{
    /**
     * @param ViewInterface $pValue
     */
    public function setView(ViewInterface $pValue);
}