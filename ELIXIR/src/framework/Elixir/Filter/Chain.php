<?php

namespace Elixir\Filter;

use Elixir\Facade\Filter;
use Elixir\Filter\FilterAbstract;
use Elixir\Filter\FilterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Chain extends FilterAbstract
{
    /**
     * @var array
     */
    protected $_filters = [];
    
    /**
     * @var array
     */
    protected $_steps = [];
    
    /**
     * @return array
     */
    public function getSteps()
    {
        return $this->_steps;
    }

    /**
     * @param FilterInterface|callable|string $pFilter
     * @param array $pOptions
     */
    public function addFilter($pFilter, array $pOptions = [])
    {
        $this->_filters[] = ['filter' => $pFilter, 'options' => $pOptions];
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
        $this->_filters = [];
        
        foreach($pData as $data)
        {
            $filter = $data;
            $options = [];
            
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
    public function filter($pContent, array $pOptions = [])
    {
        $this->_steps = [$pContent];
        
        foreach($this->_filters as $data)
        {
            if($data['filter'] instanceof FilterInterface)
            {
                $pContent = $data['filter']->filter($pContent, $data['options']);
            }
            else if(is_callable($data['filter']))
            {
                $pContent = call_user_func_array($data['filter'], [$pContent, $data['options']]);
            }
            else
            {
                $pContent = Filter::filter($data['filter'], $pContent, $data['options']);
            }
            
            $this->_steps[] = $pContent;
        }
        
        return $pContent;
    }
}
