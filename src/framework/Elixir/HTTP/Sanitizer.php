<?php

namespace Elixir\HTTP;

use Elixir\DI\ContainerInterface;
use Elixir\Filter\FilterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Sanitizer
{
    /**
     * @var ContainerInterface
     */
    protected $_container;
    
    /**
     * @param ContainerInterface $pContainer
     */
    public function __construct(ContainerInterface $pContainer = null) 
    {
        $this->_container = $pContainer;
    }
    
    /**
     * @param ContainerInterface $pValue
     */
    public function setContainer(ContainerInterface $pValue)
    {
        $this->_container = $pValue;
    }
    
    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->_container;
    }

    /**
     * @param mixed $pContent
     * @param string|integer $pFilter
     * @param array $pOptions
     * @return mixed
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function filter($pContent, $pFilter = 'FILTER_SANITIZE_FULL_SPECIAL_CHARS', array $pOptions = [])
    {
        if($pFilter === 'FILTER_SANITIZE_FULL_SPECIAL_CHARS')
        {
            if(defined('FILTER_SANITIZE_FULL_SPECIAL_CHARS'))
            {
                return filter_var($pContent, FILTER_SANITIZE_FULL_SPECIAL_CHARS, $pOptions);
            }
            else
            {
                $flags = ENT_QUOTES;

                if(defined('ENT_SUBSTITUTE'))
                {
                    $flags |= ENT_SUBSTITUTE;
                }

                return htmlspecialchars($pContent, $flags, 'UTF-8');
            }
        }
        
        if(is_int($pFilter))
        {
             return filter_var($pContent, $pFilter, $pOptions);
        }
        
        if(null === $this->_container)
        {
            throw new \RuntimeException('Container is not defined.');
        }
        
        $filter = $this->_container->get($pFilter);
        
        if(!$filter instanceof FilterInterface)
        {
            throw new \InvalidArgumentException(sprintf('Filter "%s" does not exist.', $pFilter));
        }
        
        return $filter->filter($pContent, $pOptions);
    }
}
