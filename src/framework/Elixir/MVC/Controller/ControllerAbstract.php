<?php

namespace Elixir\MVC\Controller;

use Elixir\DI\ContainerInterface;
use Elixir\Dispatcher\DispatcherInterface;
use Elixir\Helper\HelperInterface;
use Elixir\HTTP\Request;
use Elixir\MVC\Controller\ControllerInterface;
use Elixir\MVC\Controller\Helper\Container;
use Elixir\View\GlobalInterface;
use Elixir\View\HelperInterface as ViewHelperInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class ControllerAbstract implements ControllerInterface
{
    /**
     * @var DispatcherInterface
     */
    protected $_dispatcher;
    
    /**
     * @var Request
     */
    protected $_request;
    
    /**
     * @var ContainerInterface
     */
    protected $_container;
    
    /**
     * @var Container
     */
    protected $_helper;
    
    /**
     * @return array
     */
    protected function getControllerHelpers() 
    {
        return [];
    }
    
    /**
     * @return array
     */
    protected function getViewHelpers()
    {
        return [];
    }
    
    /**
     * @see ControllerInterface::initialize()
     */
    public function initialize(Request $pRequest,
                               DispatcherInterface $pDispatcher,
                               ContainerInterface $pContainer)
    {
        $this->_dispatcher = $pDispatcher;
        $this->_request = $pRequest;
        $this->_container = $pContainer;
        
        /************ CONTROLLER HELPERS ************/
        
        $this->setHelperContainer($this->_container);
        $this->_helper->load($this->getControllerHelpers());
        $this->_helper->setUseTag(true);

        /************ VIEW HELPERS ************/
        
        if($this->_container->has('view'))
        {
            $view = $this->_container->get('view');
            $view->set('_request', $this->_request);
            
            if($view instanceof GlobalInterface)
            {
                $view->globalize('_request');
            }

            $views = method_exists($view, 'getEngines') ? $view->getEngines() : (array)$views;
            
            foreach($views as $view)
            {
                if($view instanceof ViewHelperInterface)
                {
                    $view->setHelperContainer($this->_container);
                    $view->getHelperContainer()->load($this->getViewHelpers());
                    $view->getHelperContainer()->setUseTag(true);
                }
            }
        }
    }
    
    /**
     * @see ControllerInterface::getDispatcher()
     */
    public function getDispatcher()
    {
        return $this->_dispatcher;
    }
    
    /**
     * @see ControllerInterface::getRequest()
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    /**
     * @see ControllerInterface::getContainer()
     */
    public function getContainer()
    {
        return $this->_container;
    }
    
    /**
     * @param Container|ContainerInterface $pValue
     */
    public function setHelperContainer($pValue)
    {
        $this->_helper = $pValue instanceof Container ? $pValue : new Container($pValue);
        $this->_helper->setController($this);
    }
    
    /**
     * @return Container
     */
    public function getHelperContainer()
    {
        return $this->_helper;
    }
    
    /**
     * @see ControllerInterface::helper()
     */
    public function helper($pKey)
    {
        return $this->_helper->get($pKey);
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     * @return mixed;
     * @throws \NotFoundException
     * @throws \BadMethodCallException
     */
    public function __call($pMethod, $pArguments)
    {
        $helper = $this->helper('helper.' . $pMethod);
        
        if(is_callable($helper))
        {
            return call_user_func_array($helper, $pArguments);
        }
        else
        {
            $method = $helper instanceof HelperInterface ? 'direct' : 'filter';
            return call_user_func_array([$helper, $method], $pArguments);
        }
        
        throw new \BadMethodCallException(sprintf('Helper "%s" is not defined.', $pMethod));
    }
}
