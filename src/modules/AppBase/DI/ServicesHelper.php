<?php

namespace Elixir\Module\AppBase\DI;

use Elixir\DI\ContainerInterface;
use Elixir\DI\ProviderInterface;
use Elixir\Filter\Escaper;
use Elixir\Helper\Action;
use Elixir\Helper\Forward;
use Elixir\Helper\Locator;
use Elixir\Helper\Partial;
use Elixir\Helper\Renderer;
use Elixir\Helper\URL;
use Elixir\MVC\Controller\Helper\Container as ControllerHelper;
use Elixir\View\Helper\Container as ViewHelper;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class ServicesHelper implements ProviderInterface
{
    /**
     * @see ProviderInterface::load()
     */
    public function load(ContainerInterface $pContainer) 
    {
        /************ REQUEST ************/
        
        $pContainer->set('helper.request', function($pContainer)
        {
            return $pContainer->get('request');
        }, 
        [
            'type' => ContainerInterface::SINGLETON, 
            'tags' => [ViewHelper::HELPER_TAG_KEY]
        ]);
        
        /************ LOCATOR ************/
        
        $pContainer->set('helper.locator', function($pContainer)
        {
            return new Locator($pContainer->get('application'));
        }, 
        [
            'type' => ContainerInterface::SINGLETON, 
            'tags' => [
                ViewHelper::HELPER_TAG_KEY, 
                ControllerHelper::HELPER_TAG_KEY
            ]
        ]);
        
        /************ INTERNAL REDIRECTION ************/
        
        $pContainer->set('helper.forward', function()
        {
            return new Forward();
        }, 
        [
            'type' => ContainerInterface::SINGLETON, 
            'tags' => [ControllerHelper::HELPER_TAG_KEY]
        ]);
        
        /************ INTERNAL CLIENT ************/
        
        $pContainer->set('helper.action', function()
        {
            return new Action();
        }, 
        [
            'type' => ContainerInterface::SINGLETON, 
            'tags' => [ControllerHelper::HELPER_TAG_KEY]
        ]);
        
        /************ RENDER ************/
        
        $pContainer->set('helper.render', function()
        {
            return new Renderer();
        }, 
        [
            'type' => ContainerInterface::SINGLETON, 
            'tags' => [ControllerHelper::HELPER_TAG_KEY]
        ]);
        
        /************ PARTIAL ************/
        
        $pContainer->set('helper.partial', function($pContainer)
        {
            $partial = new Partial();
            $partial->setLocator($pContainer->get('helper.locator'));
            
            return $partial;
        }, 
        [
            'type' => ContainerInterface::SINGLETON, 
            'tags' => [ViewHelper::HELPER_TAG_KEY]
        ]);
        
        /************ ESCAPER ************/
        
        $pContainer->set('helper.escaper', function()
        {
            return new Escaper('UTF-8');
        }, 
        [
            'type' => ContainerInterface::SINGLETON,
            'tags' => [
                ViewHelper::HELPER_TAG_KEY, 
                ControllerHelper::HELPER_TAG_KEY
            ],
            'aliases' => ['filter.escaper']
        ]);
        
        /************ URL ************/
        
        $pContainer->set('helper.url', function($pContainer)
        {
            $URL = new URL();
            $URL->setLocator($pContainer->get('helper.locator'));
            $URL->setRouter($pContainer->get('router'));
            
            return $URL;
        }, 
        [
            'type' => ContainerInterface::SINGLETON, 
            'tags' => [
                ViewHelper::HELPER_TAG_KEY,
                ControllerHelper::HELPER_TAG_KEY
            ]
        ]);
    }
}
