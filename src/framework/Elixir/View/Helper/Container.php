<?php

namespace Elixir\View\Helper;

use Elixir\DI\ContainerInterface;
use Elixir\View\ViewInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Container 
{
    /**
     * @var string
     */
    const HELPER_TAG_KEY = 'view_helper';

    /**
     * @var ContainerInterface
     */
    protected $_container;
    
    /**
     * @var boolean 
     */
    protected $_useTag = false;
    
    /**
     * @var ViewInterface
     */
    protected $_view;
    
    /**
     * @param ContainerInterface $pContainer
     */
    public function __construct(ContainerInterface $pContainer) 
    {
        $this->_container = $pContainer;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->_container;
    }
    
    /**
     * @param ViewInterface $pValue
     */
    public function setView(ViewInterface $pValue)
    {
        $this->_view = $pValue;
    }
    
    /**
     * @return ViewInterface
     */
    public function getView()
    {
        return $this->_view;
    }
    
    /**
     * @param boolean $pValue
     */
    public function setUseTag($pValue)
    {
        $this->_useTag = $pValue;
    }
    
    /**
     * @return boolean
     */
    public function isUseTag()
    {
        return $this->_useTag;
    }
    
    /**
     * @param array $pHelpers
     */
    public function load(array $pHelpers)
    {
        foreach($pHelpers as $key => $value)
        {
            if($this->_container->has($key))
            {
                $this->_container->addTag($key, self::HELPER_TAG_KEY);
            }
            else
            {
                $options = array(
                    'type' => ContainerInterface::SINGLETON,
                    'tags' => self::HELPER_TAG_KEY
                );
                
                if(is_array($value))
                {
                    $helper = $value[0];
                    $options = array_merge($value[1], $options);
                }
                else
                {
                    $helper = $value;
                }
                
                if(!($helper instanceof \Closure))
                {
                    $helper = function() use ($helper)
                    { 
                        return new $helper(); 
                    };
                }
                
                $this->_container->set($key, $helper, $options);
            }
        }
    }
    
    /**
     * @see ContainerInterface::get();
     */
    public function get($pKey, $pDefault = null)
    {
        if($this->_useTag)
        {
            if(!$this->_container->hasTag($pKey, self::HELPER_TAG_KEY))
            {
                if(is_callable($pDefault))
                {
                    return call_user_func($pDefault);
                }

                return $pDefault;
            }
        }
        
        $helper = $this->_container->get($pKey, null, null);
        
        if(null !== $helper)
        {
            if(null !== $this->_view)
            {
                if($helper instanceof ContextInterface)
                {
                    $helper->setView($this->_view);
                }
            }
            
            return $helper;
        }
        
        if(is_callable($pDefault))
        {
            return call_user_func($pDefault);
        }
        
        return $pDefault;
    }
}
