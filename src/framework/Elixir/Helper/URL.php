<?php

namespace Elixir\Helper;

use Elixir\Helper\Locator;
use Elixir\HTTP\Request;
use Elixir\HTTP\RequestFactory;
use Elixir\MVC\Controller\ControllerInterface;
use Elixir\MVC\Controller\Helper\ContextInterface as ControllerContextInterface;
use Elixir\Routing\Generator\GeneratorInterface;
use Elixir\Routing\Route;
use Elixir\Routing\RouterInterface;
use Elixir\View\Helper\ContextInterface as TemplateContextInterface;
use Elixir\View\ViewInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class URL implements TemplateContextInterface, ControllerContextInterface, HelperInterface
{
    /**
     * @var mixed $pValue
     */
    protected $_context;
    
    /**
     * @var Request 
     */
    protected $_request;
    
    /**
     * @var Locator
     */
    protected $_locator;
    
    /**
     * @var RouterInterface
     */
    protected $_router;

    /**
     * @param Request  $pRequest
     */
    public function __construct(Request $pRequest = null)
    {
        $this->_request = $pRequest;
    }
    
    /**
     * @see \Elixir\View\Helper\ContextInterface::setView();
     */
    public function setView(ViewInterface $pValue)
    {
        $this->_context = $pValue;
    }
    
    /**
     * @see \Elixir\MVC\Controller\Helper\ContextInterface::setController();
     */
    public function setController(ControllerInterface $pValue)
    {
        $this->_context = $pValue;
    }
    
     /**
     * @param Locator $pValue
     */
    public function setLocator(Locator $pValue)
    {
        $this->_locator = $pValue;
    }
    
    /**
     * @return Locator
     */
    public function getLocator()
    {
        return $this->_locator;
    }
    
    /**
     * @param RouterInterface $pValue
     */
    public function setRouter(RouterInterface $pValue)
    {
        $this->_router = $pValue;
    }
    
    /**
     * @return RouterInterface
     */
    public function getRouter()
    {
        return $this->_router;
    }

    /**
     * @param string $pUrl
     * @return string
     */
    public function baseURL($pUrl = '')
    {
        if(null === $this->_request)
        {
            if($this->_context instanceof ControllerInterface)
            {
                $this->_request = $this->_context->getRequest();
            }
            else   
            {
                if(null !== $this->_context)
                {
                    $this->_request = $this->_context->get('_request');

                    if(null === $this->_request && method_exists($this->_context, 'helper'))
                    {
                        $this->_request = $this->_context->helper('helper.request');
                    }
                }
                
                if(null === $this->_request)
                {
                    $this->_request = RequestFactory::create();
                }
            }
        }
        
        if(null !== $this->_locator)
        {
            $pUrl = $this->_locator->locateFile($pUrl, false);
        }
        
        return $this->_request->getBaseURL() . '/' . $pUrl;
    }
    
    /**
     * @param string $pRouteName
     * @param array|string $pOptions
     * @param string $pMode
     * @return string
     * @throws \RuntimeException
     */
    public function generate($pRouteName, $pOptions = array(), $pMode = GeneratorInterface::URL_ABSOLUTE)
    {
        if(null === $this->_router)
        {
            throw new \RuntimeException('The Router class is not defined.');
        }
        
        if(is_string($pOptions))
        {
            $pOptions = array(Route::MVC => $pOptions);
        }
        
        return $this->_router->generate($pRouteName, $pOptions, $pMode);
    }
    
    /**
     * @param string $pRouteName
     * @param mixed $pDefault
     * @return Route
     */
    public function route($pRouteName, $pDefault = null)
    {
        return $this->_router->getCollection()->get($pRouteName, $pDefault);
    }

    /**
     * @see URL::baseURL()
     */
    public function direct()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'baseURL'), $args);
    }
}
