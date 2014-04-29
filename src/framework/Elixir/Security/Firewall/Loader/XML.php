<?php

namespace Elixir\Security\Firewall\Loader;

use Elixir\Security\Firewall\Loader\LoaderInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class XML implements LoaderInterface
{
    /**
     * @see LoaderInterface::load()
     */
    public function load($pConfig)
    {
        if(is_file($pConfig))
        {
            $pConfig = simplexml_load_file($pConfig);
        }
        
        if(isset($pConfig->{self::GLOBALS}))
        {
            $globals = array();
            
            foreach($pConfig->{self::GLOBALS}->children() as $key => $value)
            {
                $globals[$key] = $this->parse($value);
            }
        }
        
        $access = array();
        
        foreach($pConfig->accesscontrol as $ac)
        {
            $priority = isset($ac['priority']) ? (int)$ac['priority'] : 0;
            $regex = (string)$ac->regex;
            $options = array();
            
            if(isset($ac->options))
            {
                foreach($ac->options->children() as $key => $value)
                {
                    $options[$key] = $this->parse($value);
                }
            }
            
            if(isset($globals))
            {
                $options = array_merge($globals, $options);
            }
            
            $access[$regex] = array('options' => $options, 'priority' => $priority);
        }
        
        return $access;
    }
    
    /**
     * @param \SimpleXMLElement $pData
     * @return string|array
     */
    protected function parse(\SimpleXMLElement $pData)
    {
        if(count($pData->children()) == 0)
        {
            return (string)$pData;
        }
        else
        {
            $r = array();
            
            foreach($pData->children() as $key => $value)
            {
                if(isset($r[$key]))
                {
                     $r[$key] = (array)$r[$key];
                     $r[$key][] = $this->parse($value);
                }
                else 
                {
                     $r[$key] = $this->parse($value);
                }
            }
            
            return $r;
        }
    }
}