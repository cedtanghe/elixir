<?php

namespace Elixir\Helper;

use Elixir\MVC\ApplicationInterface;
use Elixir\MVC\Controller\ControllerInterface;
use Elixir\MVC\Controller\Helper\ContextInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 * @author Nicola Pertosa <n.pertosa@peoleo.fr>
 */

class Action implements ContextInterface, HelperInterface
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
     * @param string $pPathInfo
     * @param array $pAttributes
     * @param boolean $pResetOthersAttributes
     * @return string
     */
    public function get($pPathInfo, array $pAttributes = array(), $pResetOthersAttributes = true)
    {
        if(null === $this->_application)
        {
            $this->_application = $this->_context->getContainer()->get('application');
        }
        
        $currentRequest = $this->_context->getRequest();
        $request = clone $currentRequest;
        $request->setURL(
            rtrim($request->getBaseURL(), '/') .
            '/' . 
            ltrim(str_replace($request->getBaseURL(), '', $pPathInfo), '/')
        );
        
        if($pResetOthersAttributes)
        {
            $request->getAttributes()->sets($pAttributes);
        }
        else
        {
            $request->getAttributes()->merge($pAttributes);
        }
        
        $response = $this->_application->handle($request, ApplicationInterface::MAIN_REQUEST);
        
        // Restore the request instance in container;
        $this->_context->getContainer()->set('request', $currentRequest);
        
        return $response->getContent();
    }
    
    /**
     * @see Action::to()
     */
    public function direct()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'get'), $args);
    }
}