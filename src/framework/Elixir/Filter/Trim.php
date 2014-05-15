<?php

namespace Elixir\Filter;

use Elixir\Filter\FilterAbstract;
use Elixir\Filter\FilterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Trim extends FilterAbstract
{
    /**
     * @see FilterInterface::filter()
     */
    public function filter($pContent, array $pOptions = [])
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $charlist = isset($pOptions['charList']) ? $pOptions['charList'] : null;
        
        if(is_array($charlist))
        {
            $charlist = implode('..', $charlist);
        }
        
        return trim($pValue, $charlist);
    }
}