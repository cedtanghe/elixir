<?php

namespace Elixir\Module\Console\DI;

use Elixir\DI\ContainerInterface;
use Elixir\DI\ProviderInterface;
use Elixir\Module\Console\Command\AssetsExport;
use Elixir\Module\Console\Command\AssetsImport;
use Elixir\Module\Console\Command\ModelGenerate;
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
        
        $pContainer->set('console', function($pContainer)
        {
            $application = $pContainer->get('application');
            $console = new Application();
            
            // Create module
            $console->add(new ModuleCreate($application));
            
            // Export assets
            $console->add(new AssetsExport($application));
            
            // Import assets
            $console->add(new AssetsImport($application));
            
            // Generates models
            $console->add(new ModelGenerate($application, $pContainer));
            
            return $console;
        }, 
        array('type' => ContainerInterface::SINGLETON));
    }
}