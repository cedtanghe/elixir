<?php

namespace Elixir\Filter;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class FilterAbstract implements FilterInterface
{
    /**
     * @var array
     */
    protected $_options = array();

    /**
     * @param array $pValue
     */
    public function setDefaultOptions(array $pValue)
    {
        $this->_options = $pValue;
    }
    
    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->_options;
    }
}

