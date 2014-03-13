<?php

namespace AppExtend\DI;

use Elixir\DB\DBFactory;
use Elixir\DI\ContainerInterface;
use Elixir\HTTP\Session\Session;
use Elixir\Module\Application\DI\Services as ParentServices;

class Services extends ParentServices
{
    public function load(ContainerInterface $pContainer) 
    {
        parent::load($pContainer);
        
        /************ CONGIGURATION ************/
        
        $pContainer->extend('config', function($pConfig, $pContainer)
        {
            $pConfig->load(array(
                __DIR__ . '/../resources/configs/config.php',
                __DIR__ . '/../resources/configs/private.php'
            ),
            array('recursive' => true));
            
            return $pConfig;
        });
        
        /************ SESSION ************/
        
        $session = Session::instance();
        
        if(null === $session)
        {
            $config = $pContainer->get('config');
            $session = new Session();
            $session->setName($config->get(array('session', 'name')));
            $session->start();
        }
        
        $pContainer->singleton('session', function() use($session)
        {
            return $session;
        });
        
        /************ CONNECTIONS ************/
        
        $pContainer->singleton('DB.default', function($pContainer)
        {
            $config = $pContainer->get('config');
            return DBFactory::create($config['db']);
        });
        
        /************ ROUTER ************/
        
        $pContainer->extend('router', function($pRouter, $pContainer)
        {
            $pRouter->load(__DIR__ . '/../resources/routes/routes.php');
            return $pRouter;
        });
    }
}