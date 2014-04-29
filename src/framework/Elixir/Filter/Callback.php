<?php

namespace Elixir\Filter;

use Elixir\Filter\FilterAbstract;
use Elixir\Filter\FilterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Callback extends FilterAbstract
{
    /**
     * @see FilterInterface::filter()
     */
    public function filter($pContent, array $pOptions = array())
    {
        $pOptions = array_merge($this->_options, $pOptions);
        
        if(isset($pOptions['options']) || isset($pOptions['callback']))
        {
            $callable = isset($pOptions['options']) ? $pOptions['options'] : $pOptions['callback'];
            unset($pOptions['options']);
            unset($pOptions['callback']);
        }
        else
        {
            $callable = $pOptions[0];
            array_shift($pOptions);
        }
        
        return $callable($pContent, $pOptions);
    }
}
