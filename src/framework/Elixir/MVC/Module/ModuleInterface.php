<?php

namespace Elixir\MVC\Module;

use Elixir\DI\ContainerInterface;
use Elixir\Dispatcher\DispatcherInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface ModuleInterface
{
    /**
     * @return string
     */
    public function getName();
    
    /**
     * @return string|null
     */
    public function getParent();
    
    /**
     * @return string
     */
    public function getNamespace();
    
    /**
     * @return string
     */
    public function getPath();
    
    /**
     * @return string|array|null
     */
    public function getRequired();
    
    /**
     * @param DispatcherInterface $pDispatcher
     * @param ContainerInterface $pContainer
     */
    public function register(DispatcherInterface $pDispatcher, ContainerInterface $pContainer);
    
    public function boot();
}