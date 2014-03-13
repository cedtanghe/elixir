<?php

namespace Elixir\Filter;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Trim extends FilterAbstract
{
    /**
     * @see FilterInterface::filter()
     */
    public function filter($pContent, array $pOptions = array())
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