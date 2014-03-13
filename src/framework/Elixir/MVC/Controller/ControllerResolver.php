<?php

namespace Elixir\MVC\Controller;

use Elixir\Util\Str;
use Elixir\HTTP\Request;
use Elixir\MVC\ApplicationInterface;
use Elixir\MVC\Controller\ControllerInterface;
use Elixir\MVC\Exception\NotFoundException;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
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
        
        if(preg_match('/^\(@[^\)]+\)$/', $module))
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
        
        return array($controller, lcfirst(Str::camelize($action)) . 'Action');
    }
    
    /**
     * @see ApplicationInterface:getArguments()
     */
    public function getArguments(Request $pRequest, $pController)
    {
        return array();
    }
}
