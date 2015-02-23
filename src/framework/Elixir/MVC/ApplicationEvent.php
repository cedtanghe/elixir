<?php

namespace Elixir\MVC;

use Elixir\Dispatcher\Event;
use Elixir\HTTP\Request;
use Elixir\HTTP\Response;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class ApplicationEvent extends Event
{
    /**
     * @var string
     */
    const START = 'start';
    
    /**
     * @var string
     */
    const COMPLETE = 'complete';
    
    /**
     * @var string
     */
    const MODULES_BOOTED = 'modules_booted';
    
    /**
     * @var string
     */
    const FILTER_REQUEST = 'filter_request';
    
    /**
     * @var string
     */
    const PRE_CONTROLLER = 'pre_controller';
    
    /**
     * @var string
     */
    const POST_CONTROLLER = 'post_controller';
    
    /**
     * @var string
     */
    const FILTER_RESPONSE = 'filter_response';
    
    /**
     * @var string
     */
    const TERMINATE = 'terminate';
    
    /**
     * @var string
     */
    const EXCEPTION_403 = 'exception_403';
    
    /**
     * @var string
     */
    const EXCEPTION_404 = 'exception_404';
    
    /**
     * @var string
     */
    const EXCEPTION_500 = 'exception_500';

    /**
     * @var Request
     */
    protected $_request;
    
    /**
     * @var string
     */
    protected $_requestType;
    
    /**
     * @var Response
     */
    protected $_response;
    
    /**
     * @var \Exception
     */
    protected $_exception;

    /**
     * @see Event::__contruct()
     * @param Request $pRequest
     * @param string $pRequestType
     * @param Response $pResponse
     * @param \Exception $pException
     */
    public function __construct($pType,
                                Request $pRequest = null, 
                                $pRequestType = null,
                                Response $pResponse = null,
                                \Exception $pException = null) 
    {
        parent::__construct($pType);
        
        $this->_request = $pRequest;
        $this->_requestType = $pRequestType;
        $this->_response = $pResponse;
        $this->_exception = $pException;
    }
    
    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    /**
     * @return string
     */
    public function getRequestType()
    {
        return $this->_requestType;
    }
    
    /**
     * @param Response $pValue
     */
    public function setResponse(Response $pValue)
    {
        $this->_response = $pValue;
    }
    
    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->_response;
    }
    
    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->_exception;
    }
}
