<?php

namespace Elixir\Util;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Str
{
    /**
     * @param mixed $pObject
     * @return boolean
     */
    public static function isJSON($pObject)
    {
        try 
        {
            return  null !== $pObject && null !== @json_decode($pObject);
        } 
        catch(\Exception $e) 
        {
            return false;
        }
    }
    
    /**
     * @param integer $pLength
     * @param string|array $pCharlist
     * @return string
     */
    public static function random($pLength = 10, $pCharlist = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        if(is_array($pCharlist))
        {
            $pCharlist = implode('', $pCharlist);
        }
        
        return substr(str_shuffle($pCharlist), 0, $pLength);
    }
    
    /**
     * @param string $pStr
     * @param string $pCharset
     * @return string
     */
    public static function removeAccents($pStr, $pCharset = 'utf-8')
    {
        $str = htmlentities($pStr, ENT_NOQUOTES, $pCharset);
        $str = preg_replace('/&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);/', '\1', $str);
        $str = preg_replace('/&([A-za-z]{2})(?:lig);/', '\1', $str);
        $str = preg_replace('/&[^;]+;/', '', $str);

        return $str;
    }
    
    /**
     * @param string $pStr
     * @return string
     */
    public static function camelize($pStr)
    {
        return preg_replace(
            '/[^a-z0-9]+/i',
            '', 
            ucwords(
                str_replace(
                    array('-', '_', '.'), 
                    ' ', 
                    static::removeAccents($pStr)
                )
            )
        );
    }
    
    /**
     * @param string $pStr
     * @param string $pSeparator
     * @return string
     */
    public static function slug($pStr, $pSeparator = '-')
    {
        $pStr = preg_replace('/[^\p{L}\p{Nd}]+/u', $pSeparator, static::removeAccents($pStr));
        $pStr = preg_replace('/(' . preg_quote($pSeparator, '/') . '){2,}/', '$1', $pStr);
        $pStr = trim($pStr, $pSeparator);

        return $pStr;
    }
    
    /**
     * @param string $pStr
     * @param integer $pMax
     * @param string $pCut
     * @param boolean $pUseWord
     * @return string
     */
    public static function resume($pStr, $pMax = 100, $pCut = '...', $pUseWord = true)
    {
        $str = strip_tags($pStr);
        
        if(strlen($str) > $pMax)
        {
            $to = $pMax - strlen($pCut);
            $result = substr($str, 0, $to);
            
            if($pUseWord)
            {
                $start = explode(' ', substr($str, 0, $to + 25));
                $end = explode(' ', $result);
                
                if(end($start) != end($end))
                {
                    array_pop($end);
                    $result = implode(' ', $end);
                }
            }
            
            $str = rtrim($result) . $pCut;
        }
        
        return $str;
    }
    
    /**
     * @param string $pStr
     * @param integer $pSensitivity
     * @param integer $pMin
     * @param integer $pMax
     * @param integer $pLimit
     * @return array
     */
    public static function keywords($pStr, $pSensitivity = 4, $pMin = 2, $pMax = 8, $pLimit = 10)
    {
        $words = array_count_values(explode(' ', preg_replace('/[\p{P}\p{S}]/', '', strip_tags($pStr))));
        asort($words);
        
        foreach($words as $key => $value)
        {
            if(strlen($key) < $pSensitivity)
            {
                unset($words[$key]);
            }
        }
        
        $total = count($words);
        
        foreach($words as $key => $value)
        {
            $density = ($value / $total) * 100;
            
            if($density < $pMin || $density > $pMax)
            {
                unset($words[$key]);
            }
        }
        
        return array_keys(array_splice($words, 0, $pLimit));
    }
}