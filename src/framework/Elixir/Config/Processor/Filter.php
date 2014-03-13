<?php

namespace Elixir\Config\Processor;

use Elixir\Config\ConfigInterface;
use Elixir\Filter\FilterInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
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
    protected $_filterOptions = array();
    
    /**
     * @param FilterInterface $pFilter
     * @param array $pOptions
     */
    public function __construct(FilterInterface $pFilter, array $pOptions = array()) 
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
        
        if(is_array($pValue) || is_object($pValue) || $pValue instanceof Traversable)
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
