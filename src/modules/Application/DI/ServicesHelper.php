<?php

namespace Elixir\Module\Application\DI;

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
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
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
        array(
            'type' => ContainerInterface::SINGLETON, 
            'tags' => array(ViewHelper::HELPER_TAG_KEY)
        ));
        
        /************ LOCATOR ************/
        
        $pContainer->set('helper.locator', function($pContainer)
        {
            return new Locator($pContainer->get('application'));
        }, 
        array(
            'type' => ContainerInterface::SINGLETON, 
            'tags' => array(
                ViewHelper::HELPER_TAG_KEY, 
                ControllerHelper::HELPER_TAG_KEY
            )
        ));
        
        /************ INTERNAL REDIRECTION ************/
        
        $pContainer->set('helper.forward', function()
        {
            return new Forward();
        }, 
        array(
            'type' => ContainerInterface::SINGLETON, 
            'tags' => array(ControllerHelper::HELPER_TAG_KEY)
        ));
        
        /************ INTERNAL CLIENT ************/
        
        $pContainer->set('helper.action', function()
        {
            return new Action();
        }, 
        array(
            'type' => ContainerInterface::SINGLETON, 
            'tags' => array(ControllerHelper::HELPER_TAG_KEY)
        ));
        
        /************ RENDER ************/
        
        $pContainer->set('helper.render', function()
        {
            return new Renderer();
        }, 
        array(
            'type' => ContainerInterface::SINGLETON, 
            'tags' => array(ControllerHelper::HELPER_TAG_KEY)
        ));
        
        /************ PARTIAL ************/
        
        $pContainer->set('helper.partial', function($pContainer)
        {
            $partial = new Partial();
            $partial->setLocator($pContainer->get('helper.locator'));
            
            return $partial;
        }, 
        array(
            'type' => ContainerInterface::SINGLETON, 
            'tags' => array(ViewHelper::HELPER_TAG_KEY)
        ));
        
        /************ ESCAPER ************/
        
        $pContainer->set('helper.escaper', function()
        {
            return new Escaper('UTF-8');
        }, 
        array(
            'type' => ContainerInterface::SINGLETON,
            'tags' => array(ViewHelper::HELPER_TAG_KEY),
            'aliases' => array('filter.escaper')
        ));
        
        /************ URL ************/
        
        $pContainer->set('helper.url', function($pContainer)
        {
            $URL = new URL();
            $URL->setLocator($pContainer->get('helper.locator'));
            $URL->setRouter($pContainer->get('router'));
            
            return $URL;
        }, 
        array(
            'type' => ContainerInterface::SINGLETON, 
            'tags' => array(
                ViewHelper::HELPER_TAG_KEY,
                ControllerHelper::HELPER_TAG_KEY
            )
        ));
    }
}
