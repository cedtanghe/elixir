<?php

namespace Elixir\Module\Twig\DI;

use Elixir\DI\ContainerInterface;
use Elixir\DI\ProviderInterface;
use Elixir\Module\Twig\View\Twig;

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
        $app = $pContainer->get('application');
        
        /************ VIEW ************/
        
        $pContainer->extend('view', function($pManager) use($app)
        {
            // Twig
            $twig = new Twig();
            
            // Extension
            $extension = $app->locateClass('(@Twig)\Extension\Extension');
            $twig->addExtension(new $extension($twig));
            
            $pManager->registerEngine(
                'twig',  
                $twig, 
                '^(html|twig)$', 
                false
            );
            
            return $pManager;
        });
    }
}