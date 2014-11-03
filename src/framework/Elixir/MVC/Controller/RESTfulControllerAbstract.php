<?php

namespace Elixir\MVC\Controller;

use Elixir\HTTP\Request;
use Elixir\MVC\Controller\ControllerAbstract;
use Elixir\MVC\Controller\RESTfulControllerInterface;
use Elixir\MVC\Exception\NotFoundException;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class RESTFulControllerAbstract extends ControllerAbstract implements RESTfulControllerInterface
{
    /**
     * @var boolean
     */
    protected $_strict = false;
    
    /**
     * @see RESTfulControllerInterface::getRestFulMethodName()
     */
    public function getRestFulMethodName($pMethod, Request $pRequest = null)
    {
        $request = $pRequest ?: $this->_request;
        $requestMethod = $request->getRequestMethod('GET');
        $prefixs = [$requestMethod];

        if(!$this->_strict)
        {
            switch($requestMethod)
            {
                case 'HEAD':
                    $prefixs[] = 'GET';
                break;
                case 'PUT':
                case 'PATCH':
                case 'DELETE':
                case 'TRACE':
                case 'CONNECT':
                case 'OPTIONS':
                    $prefixs[] = 'POST';
                break;
            }
        }

        foreach($prefixs as $prefix)
        {
            $m = strtolower($prefix) . ucfirst($pMethod);

            if(method_exists($this, $m))
            {
                return $m;
            }
        }
        
        return null;
    }

    /**
     * @see ControllerAbstract::__call()
     * @throws NotFoundException
     */
    public function __call($pMethod, $pArguments)
    {
        if(substr($pMethod, -6) == 'Action')
        {
            $m = $this->getRestFulMethodName($pMethod);
            
            if(null !== $m)
            {
                return call_user_func_array([$this, $m], $pArguments);
            }
            
            throw new NotFoundException(
                sprintf(
                    'Action "%s" could not be recovered for the request method "%s".',
                    substr($pMethod, 0, -6),
                    $requestMethod
                )
            );
        }
        
        parent::__call($pMethod, $pArguments);
    }
}
