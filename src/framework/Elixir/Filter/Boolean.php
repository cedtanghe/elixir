<?php

namespace Elixir\Filter;

use Elixir\Filter\FilterAbstract;
use Elixir\Filter\FilterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Boolean extends FilterAbstract
{
    /**
     * @see FilterInterface::filter()
     */
    public function filter($pContent, array $pOptions = [])
    {
        $pOptions = array_merge($this->_options, $pOptions);
        
        if(isset($pOptions['search']))
        {
            $search = (array)$pOptions['search'];
        }
        else
        {
            $search = [true, 'true', 'TRUE', 'yes', 'YES', '1', 1];
        }
        
        return in_array($pContent, $search, isset($pOptions['strict']) ? $pOptions['strict'] : true);
    }
}