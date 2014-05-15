<?php

namespace Elixir\Util;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Arr
{
    /**
     * @param mixed $pKey
     * @param array $pData
     * @return boolean
     */
    public static function has($pKey, array $pData)
    {
        $segments = (array)$pKey;
        $data = $pData;
        
        foreach($segments as $segment)
        {
            if(!is_array($data) || !array_key_exists($segment, $data))
            {
                return false;
            }
            
            $data = $data[$segment];
        }
        
        return true;
    }
    
    /**
     * @param mixed $pKey
     * @param array $pData
     * @param mixed $pDefault
     * @return mixed
     */
    public static function get($pKey, array $pData, $pDefault = null)
    {
        $segments = (array)$pKey;
        $data = $pData;
        
        foreach($segments as $segment)
        {
            if(!is_array($data) || !array_key_exists($segment, $data))
            {
                return is_callable($pDefault) ? $pDefault() : $pDefault;
            }
            
            $data = $data[$segment];
        }
        
        return $data;
    }
    
    /**
     * @param mixed $pKey
     * @param mixed $pValue
     * @param array $pData
     */
    public static function set($pKey, $pValue, array &$pData)
    {
        $segments = (array)$pKey;
        
        while(count($segments) > 1)
        {
            $segment = array_shift($segments);
            
            if(!is_array($pData) || !array_key_exists($segment, $pData))
            {
                $pData[$segment] = [];
            }
            
            $pData = &$pData[$segment];
        }
        
        $pData[array_shift($segments)] = $pValue;
    }
    
    /**
     * @param mixed $pKey
     * @param array $pData
     */
    public static function remove($pKey, array &$pData)
    {
        $segments = (array)$pKey;
        
        while(count($segments) > 1)
        {
            $segment = array_shift($segments);
            
            if(!is_array($pData) || !array_key_exists($segment, $pData))
            {
                return;
            }
            
            $pData = &$pData[$segment];
        }
        
        unset($pData[array_shift($segments)]);
    }
    
    /**
     * @param array $pA
     * @param array $pB
     * @return array
     */
    public static function merge(array $pA, array $pB)
    {
        foreach($pB as $key => $value) 
        {
            if(array_key_exists($key, $pA)) 
            {
                if(is_int($key)) 
                {
                    $pA[] = $value;
                } 
                elseif(is_array($value) && is_array($pA[$key]))
                {
                    $pA[$key] = static::merge($pA[$key], $value);
                }
                else 
                {
                    $pA[$key] = $value;
                }
            } 
            else 
            {
                $pA[$key] = $value;
            }
        }

        return $pA;
    }
}