<?php

namespace Elixir\Config\Loader;

use Elixir\Config\Loader\LoaderAbstract;
use Elixir\Config\Loader\LoaderInterface;
use Elixir\Util\Arr as ArrayUtils;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Arr extends LoaderAbstract
{
    /**
     * @see LoaderInterface::load()
     */
    public function load($pConfig, $pRecursive = false)
    {
        if(!is_array($pConfig))
        {
            $pConfig = include $pConfig;
        }
        
        $result = [];
        $supers = [];
        
        $m = $this->_environment;
        
        if(null !== $m)
        {
            $found = false;
            
            do
            {
                foreach($pConfig as $key => $value)
                {
                    $k = explode(':', $key);

                    if(trim($k[0]) === $m)
                    {
                        $found = true;
                        $supers[] = $value;

                        if(isset($k[1]))
                        {
                            $m = trim($k[1]);
                            continue 2;
                        }
                    }
                }
                
                $m = null;
            }
            while(null !== $m);
            
            if(!$found && !$this->_strict)
            {
                $supers[] = $pConfig;
            }
        }
        else
        {
            $supers[] = $pConfig;
        }
        
        foreach(array_reverse($supers) as $data)
        {
            $data = $this->parse($data, $pRecursive);
            $result = $pRecursive ? ArrayUtils::merge($result, $data) : array_merge($result, $data);
        }
        
        return $result;
    }
    
    /**
     * @see LoaderAbstract::parse();
     */
    protected function parse($pData, $pRecursive = false)
    {
        foreach($pData as $key => &$value)
        {  
            if(is_array($value))
            {
                $value = $this->parse($value, $pRecursive);
            }
        }
        
        return $pData;
    }
}
