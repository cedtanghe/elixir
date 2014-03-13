<?php

namespace Elixir\Filter;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Float extends FilterAbstract
{
    /**
     * @see FilterInterface::filter()
     */
    public function filter($pContent, array $pOptions = array())
    {
        $pOptions = array_merge($this->_options, $pOptions);
        
        return filter_var(
            $pValue, 
            FILTER_SANITIZE_NUMBER_FLOAT, 
            isset($pOptions['flags']) ? $pOptions['flags'] : null
        );
    }
}