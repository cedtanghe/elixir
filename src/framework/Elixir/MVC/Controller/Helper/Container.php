<?php

namespace Elixir\MVC\Controller\Helper;

use Elixir\DI\ContainerInterface;
use Elixir\MVC\Controller\ControllerInterface;
use Elixir\MVC\Controller\Helper\ContextInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Container 
{
    /**
     * @var string
     */
    const HELPER_TAG_KEY = 'controller_helper';
    
    /**
     * @var ContainerInterface $pContainer
     */
    protected $_container;
    
    /**
     * @var boolean 
     */
    protected $_useTag = false;
    
    /**
     * @var ControllerInterface 
     */
    protected $_controller;
    
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
     * @return ControllerInterface
     */
    public function setController(ControllerInterface $pValue)
    {
        $this->_controller = $pValue;
    }
    
    /**
     * @return ControllerInterface
     */
    public function getController()
    {
        return $this->_controller;
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
                $options = [
                    'type' => ContainerInterface::SINGLETON,
                    'tags' => self::HELPER_TAG_KEY
                ];
                
                if(is_array($value))
                {
                    $helper = $value[0];
                    $options = array_merge($value[1], $options);
                }
                else
                {
                    $helper = $value;
                }
                
                if(!is_callable($helper))
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
        if($this->has($pKey))
        {
            $helper = $this->_container->get($pKey);
            
            if(null !== $this->_controller)
            {
                if($helper instanceof ContextInterface)
                {
                    $helper->setController($this->_controller);
                }
            }
            
            return $helper;
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @param string $pKey
     * @return boolean
     */
    public function has($pKey)
    {
        if($this->_useTag)
        {
            if(!$this->_container->hasTag($pKey, self::HELPER_TAG_KEY))
            {
                return false;
            }
        }
        
        return $this->_container->has($pKey);
    }
}
