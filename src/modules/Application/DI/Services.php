<?php

namespace Elixir\Module\Application\DI;

use Elixir\Config\Config;
use Elixir\DI\ContainerInterface;
use Elixir\DI\ProviderInterface;
use Elixir\Routing\Collection;
use Elixir\Routing\Generator\URLGenerator;
use Elixir\Routing\Matcher\URLMatcher;
use Elixir\Routing\Router;
use Elixir\View\Manager;
use Elixir\View\PHP\PHP;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Services implements ProviderInterface
{
    /**
     * @see ProviderInterface::load()
     */
    public function load(ContainerInterface $pContainer) 
    {
        /************ CONGIG ************/
        
        $pContainer->set('config', function()
        {
            return new Config(defined('APPLICATION_ENV') ? APPLICATION_ENV : null);
        }, 
        array('type' => ContainerInterface::SINGLETON));
        
        /************ ROUTER ************/
        
        $pContainer->set('router', function()
        {
            $router = new Router(new Collection());
            $router->setURLMatcher(new URLMatcher());
            $router->setURLGenerator(new URLGenerator());
            
            return $router;
        }, 
        array('type' => ContainerInterface::SINGLETON));
        
        /************ VIEW ************/
        
        $pContainer->set('view', function($pContainer)
        {
            $manager = new Manager();
            
            // PHP
            $PHP = new PHP();
            $PHP->setEscaper($pContainer->get('helper.escaper'));
            
            $manager->registerEngine('PHP', $PHP, '^(phtml|php)$', true);
            
            return $manager;
        }, 
        array('type' => ContainerInterface::SINGLETON));
    }
}