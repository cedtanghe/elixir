<?php

namespace Elixir\Helper;

use Elixir\HTTP\Response;
use Elixir\MVC\ApplicationInterface;
use Elixir\MVC\Controller\ControllerInterface;
use Elixir\MVC\Controller\Helper\ContextInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Forward implements ContextInterface, HelperInterface
{
    /**
     * @var mixed
     */
    protected $_context;
    
    /**
     * @var ApplicationInterface 
     */
    protected $_application;
    
    /**
     * @param ApplicationInterface $pApplication
     */
    public function __construct(ApplicationInterface $pApplication = null)
    {
        $this->_application = $pApplication;
    }
    
    /**
     * @see ContextInterface::setController();
     */
    public function setController(ControllerInterface $pValue)
    {
        $this->_context = $pValue;
    }
    
    /**
     * @param string $pModule
     * @param string $pController
     * @param string $pAction
     * @param array $pAttributes
     * @param boolean $pResetOthersAttributes
     * @return Response
     */
    public function to($pModule = null,
                       $pController = null,
                       $pAction = null,
                       array $pAttributes = array(),
                       $pResetOthersAttributes = true)
    {
        if(null === $this->_application)
        {
            $this->_application = $this->_context->getContainer()->get('application');
        }
        
        $request = clone $this->_context->getRequest();
        $route = $request->getRoute();
        $module = $pModule ?: $request->getModule();
        $controller = $pController ?: $request->getController();
        $action = $pAction ?: $request->getAction();
        
        if($pResetOthersAttributes)
        {
            $request->getAttributes()->sets($pAttributes);
        }
        else
        {
            $request->getAttributes()->merge($pAttributes);
        }
        
        if(null !== $route)
        {
            $request->setRoute($route);
        }
        
        $request->setModule($module);
        $request->setController($controller);
        $request->setAction($action);
        
        return $this->_application->handle($request, ApplicationInterface::SUB_REQUEST);
    }
    
    /**
     * @see Forward::to()
     */
    public function direct()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'to'), $args);
    }
}
