<?php

namespace Elixir\MVC\Module;

use Elixir\DI\ContainerInterface;
use Elixir\Dispatcher\DispatcherInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

abstract class ModuleAbstract implements ModuleInterface
{
    /**
     * @var DispatcherInterface
     */
    protected $_dispatcher;
    
    /**
     * @var ContainerInterface
     */
    protected $_container;
    
    /**
     * @var string 
     */
    protected $_name;
    
    /**
     * @var string 
     */
    protected $_namespace;
    
    /**
     * @var string 
     */
    protected $_path;

    /**
     * @see ModuleInterface::getName()
     */
    public function getName()
    {
        if(null === $this->_name)
        {
            $this->_name = basename($this->getPath());
        }
        
        return $this->_name;
    }
    
    /**
     * @see ModuleInterface::getParent()
     */
    public function getParent()
    {
        return null;
    }
    
    /**
     * @see ModuleInterface::getNamespace()
     */
    public function getNamespace()
    {
        if(null === $this->_namespace)
        {
            $rc = new \ReflectionClass($this);
            $this->_namespace = $rc->getNamespaceName();
        }
        
        return $this->_namespace;
    }
    
    /**
     * @see ModuleInterface::getPath()
     */
    public function getPath() 
    {
        if(null === $this->_path)
        {
            $rc = new \ReflectionClass($this);
            $this->_path = pathinfo($rc->getFileName(), PATHINFO_DIRNAME);
        }
        
        return $this->_path;
    }
    
    /**
     * @see ModuleInterface::register()
     */
    public function register(DispatcherInterface $pDispatcher, ContainerInterface $pContainer)
    {
        $this->_dispatcher = $pDispatcher;
        $this->_container = $pContainer;
    }
}