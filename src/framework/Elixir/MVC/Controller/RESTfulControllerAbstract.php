<?php

namespace Elixir\MVC\Controller;

use Elixir\Helper\HelperInterface;
use Elixir\MVC\Exception\NotFoundException;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class RESTFulControllerAbstract extends ControllerAbstract
{
    /**
     * @see ControllerAbstract::__call()
     * @throws NotFoundException
     */
    public function __call($pMethod, $pArguments)
    {
        if(substr($pMethod, -6) == 'Action')
        {
            $requestMethod = $this->_request->getRequestMethod('GET');
            $prefixs = array($requestMethod);
            
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
            
            $prefixs = array_unique($prefixs);
            
            foreach($prefixs as $prefix)
            {
                $m = strtolower($prefix) . ucfirst($pMethod);

                if(method_exists($this, $m))
                {
                    return call_user_func_array(array($this, $m), $pArguments);
                }
            }

            throw new NotFoundException(
                sprintf(
                    'Action "%s" could not be recovered for the request method "%s".',
                    substr($pMethod, 0, -6),
                    $requestMethod
                )
            );
        }
        
        $helper = $this->helper('helper.' . $pMethod);
        
        if(null !== $helper)
        {
            $method = $helper instanceof HelperInterface ? 'direct' : 'filter';
            return call_user_func_array(array($helper, $method), $pArguments);
        }
        
        throw new \BadMethodCallException(sprintf('Helper "%s" is not defined.', $pMethod));
    }
}
