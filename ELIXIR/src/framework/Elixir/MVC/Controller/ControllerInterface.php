<?php

namespace Elixir\MVC\Controller;

use Elixir\DI\Container;
use Elixir\DI\ContainerInterface;
use Elixir\Dispatcher\DispatcherInterface;
use Elixir\HTTP\Request;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface ControllerInterface
{
    /**
     * @param Request $pRequest
     * @param DispatcherInterface $pDispatcher
     * @param Container $pContainer
     */
    public function initialize(Request $pRequest,
                               DispatcherInterface $pDispatcher,
                               ContainerInterface $pContainer);
    
    /**
     * @return Request
     */
    public function getRequest();
    
    /**
     * @return DispatcherInterface
     */
    public function getDispatcher();
    
    /**
     * @return ContainerInterface
     */
    public function getContainer();
    
    /**
     * @param string $pKey
     * @return mixed
     */
    public function helper($pKey);
}
