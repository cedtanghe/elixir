<?php

namespace Elixir\I18N;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Locale
{
    /**
     * @var string
     */
    protected static $_locale;
    
    /**
     * @param string $pHeader
     * @return string
     */
    public static function acceptFromHttp($pHeader = null)
    {
        if(null === $pHeader)
        {
            $pHeader = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }
        
        if(class_exists('\Locale'))
        {
            return \Locale::acceptFromHttp($pHeader);
        }
        else
        {
            $code = explode(';', $pHeader);
            $code = explode(',', $code['0']);

            return $code['0'];
        }
    }
    
    /**
     * @param string $pLocale
     */
    public static function setDefault($pLocale = 'fr-FR')
    {
        if(class_exists('\Locale'))
        {
            \Locale::setDefault($pLocale);
        }
        else
        {
            static::$_locale = $pLocale;
        }
    }
    
    /**
     * @return string
     */
    public static function getDefault()
    {
        if(class_exists('\Locale'))
        {
            return \Locale::getDefault();
        }
        else
        {
            if(null === static::$_locale)
            {
                static::$_locale = static::acceptFromHttp();
            }
            
            return static::$_locale;
        }
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     * @return mixed
     * @throws \RuntimeException
     */
    public static function __callStatic($pMethod, $pArguments) 
    {
        if(class_exists('\Locale'))
        {
            return call_user_func_array(['\Locale', $pMethod], $pArguments);
        }
        
        throw new \RuntimeException('Class "\Locale" does not exist, please install the "intl" extension.');
    }
}
