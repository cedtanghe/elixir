<?php

namespace Elixir\Filter;

use Elixir\Filter\FilterAbstract;
use Elixir\Filter\FilterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Replace extends FilterAbstract
{
    /**
     * @see FilterInterface::filter()
     */
    public function filter($pContent, array $pOptions = [])
    {
        $pOptions = array_merge($this->_options, $pOptions);
        return preg_replace($pOptions['regex'], isset($pOptions['by']) ? $pOptions['by'] : '', $pContent);
    }
}
