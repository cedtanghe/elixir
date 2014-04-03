<?php

namespace {NAMESPACE}\DI;

use Elixir\DI\ContainerInterface;
use Elixir\DI\ProviderInterface;

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
        /************ ROUTER ************/
        
        $pContainer->extend('router', function($pRouter, $pContainer)
        {
            $pRouter->load(__DIR__ . '/../resources/routes/routes.php');
            return $pRouter;
        });
    }
}