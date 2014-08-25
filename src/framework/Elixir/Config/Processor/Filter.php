<?php

namespace Elixir\Config\Processor;

use Elixir\Config\ConfigInterface;
use Elixir\Config\Processor\ProcessorAbstract;
use Elixir\Filter\FilterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Filter extends ProcessorAbstract
{
    /**
     * @var FilterInterface 
     */
    protected $_filter;
    
    /**
     * @var array 
     */
    protected $_filterOptions = [];
    
    /**
     * @param FilterInterface $pFilter
     * @param array $pOptions
     */
    public function __construct(FilterInterface $pFilter, array $pOptions = []) 
    {
        $this->_filter = $pFilter;
        $this->_filterOptions = $pOptions;
    }
    
    /**
     * @see ProcessorInterface::process()
     */
    public function process($pValue)
    {
        if($pValue instanceof ConfigInterface)
        {
            return $this->processConfig($pValue);
        }
        
        if(is_array($pValue) || is_object($pValue) || $pValue instanceof \Traversable)
        {
            foreach($pValue as &$value)
            {
                $value = $this->process($value);
            }
        }
        else
        {
            $pValue = $this->_filter->filter($pValue, $this->_filterOptions);
        }
        
        return $pValue;
    }
}

