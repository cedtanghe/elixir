<?php

namespace Elixir\Filter;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Chain extends FilterAbstract
{
    /**
     * @var array
     */
    protected $_filters = array();
    
    /**
     * @var array
     */
    protected $_steps = array();
    
    /**
     * @return array
     */
    public function getSteps()
    {
        return $this->_steps;
    }

    /**
     * @param FilterInterface $pFilter
     * @param array $pOptions
     */
    public function addFilter($pFilter, array $pOptions = array())
    {
        $this->_filters[] = array('filter' => $pFilter, 'options' => $pOptions);
    }
    
    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->_filters;
    }
    
    /**
     * @param array $pData
     */
    public function setFilters(array $pData)
    {
        $this->_filters = array();
        
        foreach($pData as $data)
        {
            $filter = $data;
            $options = array();
            
            if(is_array($data))
            {
                $filter = $data['filter'];
                
                if(isset($data['options']))
                {
                    $options = $data['options'];
                }
            }
            
            $this->addFilter($filter, $options);
        }
    }
    
    /**
     * @see FilterInterface::filter()
     */
    public function filter($pContent, array $pOptions = array())
    {
        $this->_steps = array($pContent);
        
        foreach($this->_filters as $data)
        {
            $pContent = $data['filter']->filter($pContent, $data['options']);
            $this->_steps[] = $pContent;
        }
        
        return $pContent;
    }
}