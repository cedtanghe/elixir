<?php

namespace Elixir\Config\Loader;

use Elixir\Config\Loader\LoaderAbstract;
use Elixir\Config\Loader\LoaderInterface;
use Elixir\Util\Arr as ArrayUtils;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class XML extends LoaderAbstract
{
    /**
     * @see LoaderInterface::load()
     */
    public function load($pConfig, $pRecursive = false)
    {
        if(is_file($pConfig))
        {
            $pConfig = simplexml_load_file($pConfig);
        }
        
        $result = [];
        $supers = [];
        
        $m = $this->_environment;
        
        if(null !== $m)
        {
            if(!$this->_strict && !isset($pConfig->{$m}))
            {
                $supers[] = $pConfig;
            }
            else
            {
                do
                {
                    $item = isset($pConfig->{$m}) ? $pConfig->{$m} : null;

                    if (null !== $item)
                    {
                        $supers[] = $item;
                        $m = isset($item['extends']) ? $item['extends'] : null;
                    }
                    else
                    {
                        $m = null;
                    }
                }
                while (null !== $m);
            }
        }
        else
        {
            $supers[] = $pConfig;
        }
        
        foreach(array_reverse($supers) as $item)
        {
            $data = (array)$this->parse($item, $pRecursive);
            $result = $pRecursive ? ArrayUtils::merge($result, $data) : array_merge($result, $data);
        }
        
        return $result;
    }
    
    /**
     * @see LoaderAbstract::parse();
     */
    protected function parse($pData, $pRecursive = false)
    {
        if(count($pData->children()) == 0)
        {
            return (string)$pData;
        }
        else
        {
            $r = [];
            
            foreach($pData->children() as $key => $value)
            {
                if(isset($r[$key]))
                {
                    $r[$key] = (array)$r[$key];
                    $r[$key][] = $this->parse($value, $pRecursive);
                }
                else 
                {
                    $r[$key] = $this->parse($value, $pRecursive);
                }
            }
            
            return $r;
        }
    }
}
