<?php

namespace Elixir\Security\Firewall\Loader;

use Elixir\Security\Firewall\Loader\LoaderInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class JSON implements LoaderInterface
{
    /**
     * @see LoaderInterface::load()
     */
    public function load($pConfig)
    {
        if(is_file($pConfig))
        {
            $pConfig = file_get_contents($pConfig);
        }
        
        $pConfig = json_decode($pConfig, true);
        $access = array_slice($pConfig, 0);
        
        if(isset($access[self::GLOBALS]))
        {
            $globals = $access[self::GLOBALS];
            unset($access[self::GLOBALS]);
        }
        
        foreach($access as &$value)
        {
            if(!isset($value['priority']))
            {
                $value['priority'] = 0;
            }
            
            if(!isset($value['options']))
            {
                $value['options'] = [];
            }
            
            if(isset($globals))
            {
                $value['options'] = array_merge($globals, $value['options']);
            }
        }
        
        return $access;
    }
}