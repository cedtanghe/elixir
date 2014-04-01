<?php

namespace Elixir\Security\Firewall;

use Elixir\HTTP\Request;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Utils
{
    /**
     * @param Request $pRequest
     * @return string
     */
    public static function createResource(Request $pRequest)
    {
        $module = $pRequest->getModule();
        
        if(preg_match('/\(@([^\)]+)\)/', $module, $matches))
        {
            $module = $matches[1];
        }
        
        $module = preg_replace('/[^a-z0-9]+/i', '', $module);
        $controller = preg_replace('/[^a-z0-9]+/i', '', $pRequest->getController());
        $action = preg_replace('/[^a-z0-9]+/i', '', $pRequest->getAction());
        
        return strtoupper(sprintf('%s_%s_%s', $module, $controller, $action));
    }
}
