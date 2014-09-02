<?php

namespace Elixir\MVC\Controller;

use Elixir\HTTP\Request;
use Elixir\MVC\ApplicationInterface;
use Elixir\MVC\Controller\ControllerInterface;
use Elixir\MVC\Controller\ControllerResolverInterface;
use Elixir\MVC\Exception\NotFoundException;
use Elixir\Util\Str;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class ControllerResolver implements ControllerResolverInterface
{
    /**
     * @var string
     */
    const DEFAULT_MODULE = 'frontend';
    
    /**
     * @var string
     */
    const DEFAULT_CONTROLLER = 'index';
    
    /**
     * @var string
     */
    const DEFAULT_ACTION = 'index';
    
    /**
     * @see ControllerResolverInterface:getController()
     * @throws NotFoundException
     */
    public function getController(ApplicationInterface $pApplication, Request $pRequest)
    {
        $module = $pRequest->getModule() ?: self::DEFAULT_MODULE;
        $controller = $pRequest->getController() ?: self::DEFAULT_CONTROLLER;
        $action = $pRequest->getAction() ?: self::DEFAULT_ACTION;
        
        if(is_callable($controller))
        {
            return $controller;
        }
        else
        {
            if(false !== strpos($module, '(@'))
            {
                $namespace = $module;
            }
            else
            {
                $m = $pApplication->getModule(ucfirst(Str::camelize($module)));

                if(null === $m)
                {
                    throw new NotFoundException(sprintf('The module "%s" was not detected.', $module));
                }

                $namespace = $m->getNamespace();
            }

            $controller = implode(
                '\\', 
                array_map(
                    function($pPart)
                    {
                        return Str::camelize($pPart);
                    }, 
                    explode('\\', $controller)
                )
            );

            $class = $pApplication->locateClass($namespace . '\Controller\\' . $controller . 'Controller');

            if(null === $class)
            {
                throw new NotFoundException(sprintf('The controller "%s" was not detected.', $controller));
            }

            $controller = $pApplication->getContainer()->get(
                $class, 
                null, 
                function() use ($class)
                {
                    return new $class();
                }
            );

            if(!method_exists($controller, lcfirst(Str::camelize($action)) . 'Action') && !method_exists($controller, '__call'))
            {
                throw new NotFoundException(sprintf('The action "%s" was not detected.', $action));
            }

            if($controller instanceof ControllerInterface)
            {
                $controller->initialize(
                    $pRequest,
                    $pApplication,
                    $pApplication->getContainer()
                );
            }

            return [$controller, lcfirst(Str::camelize($action)) . 'Action'];
        }
    }
    
    /**
     * @see ControllerResolverInterface:getArguments()
     * @throws \BadMethodCallException
     * @throws \BadFunctionCallException
     */
    public function getArguments(Request $pRequest, $pController)
    {
        if(is_array($pController)) 
        {
            $reflection = new \ReflectionMethod($pController[0], $pController[1]);
            $type = 'method';
        } 
        else 
        {
            $reflection = new \ReflectionFunction($pController);
            $type = 'function';
        }
        
        $arguments = [];
        
        foreach($reflection->getParameters() as $parameter)
        {
            if(null !== ($value = $pRequest->getAttributes($this->parseParameter($parameter->getName()), null, false)))
            {
                $arguments[] = $value;
            }
            else if(null !== $parameter->getClass() && $parameter->getClass()->isInstance($pRequest))
            {
                $arguments[] = $pRequest;
            }
            else if($parameter->isDefaultValueAvailable())
            {
                $arguments[] = $parameter->getDefaultValue();
            }
            else
            {
                $exception = sprintf(
                    'The "%s" argument for the controller "%s" is invalid.', 
                    $parameter->getName(), 
                    $reflection->getName()
                );
                
                if($type == 'method')
                {
                    throw new \BadMethodCallException($exception);
                }
                else
                {
                    throw new \BadFunctionCallException($exception);
                }
            }
        }
        
        return $arguments;
    }
    
    /**
     * @param string $pParameter
     * @return string
     */
    protected function parseParameter($pParameter)
    {
        $parameter = $pParameter;
        
        if(preg_match('/^p[A-Z]/', $parameter))
        {
            $parameter = substr($pParameter, 1);
            
            if(!preg_match('/^[A-Z][A-Z]/', $parameter))
            {
                $parameter = lcfirst($parameter);
            }
        }
        
        return $parameter;
    }
}
