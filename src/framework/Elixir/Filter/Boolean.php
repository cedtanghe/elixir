<?php

namespace Elixir\Filter;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Boolean extends FilterAbstract
{
    /**
     * @see FilterInterface::filter()
     */
    public function filter($pContent, array $pOptions = array())
    {
        $pOptions = array_merge($this->_options, $pOptions);
        
        if(isset($pOptions['search']))
        {
            $search = (array)$pOptions['search'];
        }
        else
        {
            $search = array(true, 'true', 'TRUE', 'yes', 'YES', '1', 1);
        }
        
        return in_array($pContent, $search, isset($pOptions['strict']) ? $pOptions['strict'] : true);
    }
}