<?php

namespace Elixir\Module\Console\DI;

use Elixir\DI\ContainerInterface;
use Elixir\DI\ProviderInterface;
use Elixir\Module\Console\Command\GreetCommand;
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
            $console = new Application();
            $console->add(new GreetCommand());
            
            return $console;
        }, 
        array('type' => ContainerInterface::SINGLETON));
    }
}