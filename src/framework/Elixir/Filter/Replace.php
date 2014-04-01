<?php

namespace Elixir\Filter;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Replace extends FilterAbstract
{
    /**
     * @see FilterInterface::filter()
     */
    public function filter($pContent, array $pOptions = array())
    {
        $pOptions = array_merge($this->_options, $pOptions);
        return preg_replace($pOptions['regex'], isset($pOptions['by']) ? $pOptions['by'] : '', $pContent);
    }
}
