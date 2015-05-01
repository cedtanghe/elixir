<?php

namespace Elixir\Module\Console\DI;

use Elixir\DI\ContainerInterface;
use Elixir\DI\ProviderInterface;
use Elixir\Module\Console\Command\AssetsExport;
use Elixir\Module\Console\Command\AssetsImport;
use Elixir\Module\Console\Command\CacheClear;
use Elixir\Module\Console\Command\DBFixtures;
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
        
        $pContainer->set('console', function(ContainerInterface $pContainer)
        {
            $application = $pContainer->get('application');
            $console = new Application();
            
            // Create module
            $console->add(new ModuleCreate($application));
            
            // Export assets
            $console->add(new AssetsExport($application));
            
            // Import assets
            $console->add(new AssetsImport($application));
            
            // Generate models
            $console->add(new ModelGenerate($application, $pContainer));
            
            // Cache clear
            $console->add(new CacheClear($pContainer));
            
            // DB seed
            $console->add(new DBFixtures($application, $pContainer));
            
            return $console;
        }, 
        ['type' => ContainerInterface::SHARED]);
    }
}
