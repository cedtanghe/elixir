<?php

namespace Elixir\Module\Console\DI;

use Elixir\DI\ContainerInterface;
use Elixir\DI\ProviderInterface;
use Elixir\Module\Console\Command\AssetsExport;
use Elixir\Module\Console\Command\AssetsImport;
use Elixir\Module\Console\Command\ModuleCreate;
use Symfony\Component\Console\Application;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Services implements ProviderInterface
{
    /**
     * @see ProviderInterface::load()
     */
    public function load(ContainerInterface $pContainer) 
    {
        /************ CONSOLE ************/
        
        $pContainer->set('console', function()
        {
            $console = new Application();
            $console->add(new ModuleCreate());
            $console->add(new AssetsExport());
            $console->add(new AssetsImport());
            
            return $console;
        }, 
        array('type' => ContainerInterface::SINGLETON));
    }
}