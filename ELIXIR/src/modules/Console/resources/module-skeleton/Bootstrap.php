<?php

namespace {NAMESPACE};

use Elixir\DI\ContainerInterface;
use Elixir\MVC\Module\ModuleAbstract;
use {NAMESPACE}\DI\Services;

class Bootstrap extends ModuleAbstract
{
    {MODULE_PARENT}
    
    public function boot()
    {
        $this->_container->setLockMode(ContainerInterface::IGNORE_IF_ALREADY_EXISTS);
        $this->_container->load(new Services());
    }
}
