<?php

namespace Elixir\Routing\Generator;

use Elixir\Routing\Generator\URLGenerator;
use Elixir\Routing\Route;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class QueryGenerator extends URLGenerator
{
    /**
     * @var string 
     */
    protected $_queryKey = 'r';

    /**
     * @return string
     */
    public function getQueryKey()
    {
        return $this->_queryKey;
    }

    /**
     * @param string $pValue
     */
    public function setQueryKey($pValue)
    {
        $this->_queryKey = $pValue;
    }
    
    /**
     * @see URLGenerator::generate()
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function generate(Route $pRoute, array $pOptions = [], $pMode = self::URL_RELATIVE)
    {
        if(null === $this->_request)
        {
            throw new \RuntimeException('Request class is not defined.');
        }
        
        if($pRoute->hasOption(Route::GENERATE_FILTER))
        {
            $call = $pRoute->getOption(Route::GENERATE_FILTER);
            $result = call_user_func_array($call, [&$pOptions]);
            
            if($result)
            {
                $pOptions = $result;
            }
        }
        
        $parameters = [];
        $query = [];
        
        if($pRoute->hasParameter(Route::QUERY))
        {
            $query = $pRoute->getParameter(Route::QUERY);
        }
        
        foreach($pOptions as $key => $value)
        {
            switch($key)
            {
                case Route::MVC:
                    $mvc = explode('::', $value);

                    if(count($mvc) != 3)
                    {
                        throw new \InvalidArgumentException(sprintf('Option "%s" is not valid.', Route::MVC));
                    }

                    $parameters[Route::MODULE] = $mvc[0];
                    $parameters[Route::CONTROLLER] = $mvc[1];
                    $parameters[Route::ACTION] = $mvc[2];
                break;
                case Route::QUERY:
                case Route::QUERY_ALIAS:
                    $query = array_merge($query, $value);
                break;
                default:
                    $parameters[$key] = $value;
                break;
            }
        }
        
        $url = $pRoute->getPattern();
        
        if($pRoute->hasOption(Route::REPLACEMENTS))
        {
            foreach($pRoute->getOption(Route::REPLACEMENTS) as $key => $value)
            {
                $url = str_replace('%' . $key . '%', $value, $url);
            }
        }
        
        $url = str_replace('\\' . self::REGEX_SEPARATOR, self::REGEX_SEPARATOR, $url);
        
        $authorizeAttributes = $pRoute->hasOption(Route::ATTRIBUTES);
        $options = array_keys($pRoute->getOptions());
        $attributes = '';
        
        foreach($parameters as $key => $value)
        {
            if($pRoute->hasOption($key))
            {
                array_slice($options, array_search($key, $options), 1);
                
                if($this->_strict)
                {
                    if(!preg_match(self::REGEX_SEPARATOR . '^' . $value . '$' . self::REGEX_SEPARATOR, $key))
                    {
                        throw new \InvalidArgumentException(sprintf('Key "%s" is not valid.', $key));
                    }
                }
                
                $url = str_replace('{' . $key . '}', $value, $url);
            }
            else if(!$pRoute->hasParameter($key) && $authorizeAttributes)
            {
                $attributes .= '/' . $key . '/' . rawurlencode($value);
            }
        }
        
        foreach($options as $value)
        {
            if($value !== Route::ATTRIBUTES && $pRoute->hasOption($value))
            {
                if($this->_strict)
                {
                    if(!preg_match(self::REGEX_SEPARATOR . '^' . $pRoute->getOption($value) . '$' . self::REGEX_SEPARATOR, $pRoute->getParameter($value)))
                    {
                        throw new \InvalidArgumentException(sprintf('Key "%s" is not valid.', $key));
                    }
                }
                
                $url = str_replace('{' . $value . '}', $pRoute->getParameter($value), $url);
            }
        }
        
        foreach(array_diff(array_keys($options), array_keys($pRoute->getOptions())) as $value)
        {
            $url = str_replace('{' . $value . '}', '', $url);
        }
        
        if(!empty($attributes))
        {
            $url .= $attributes;
        }
        
        $url = preg_replace('/\((\/+)\)\?/U', '', $url);
        $url = trim(preg_replace_callback('/\((.*)\)\?/U', [$this, 'clean'], $url), '/');
        $query[$this->_queryKey] = $url;
        $url = '';
        
        if (isset($query[Route::SID]))
        {
            $url = '?' . $query[Route::SID];
            unset($query[Route::SID]);
        }
        
        $url .= (0 === strpos('?', $url) ? '&' : '?') . strtr(
            http_build_query($query),
            [
                '%2F' => '/',
                '%40' => '@',
                '%3A' => ':',
                '%3B' => ';',
                '%2C' => ',',
                '%3D' => '=',
                '%2B' => '+',
                '%21' => '!',
                '%2A' => '*',
                '%7C' => '|'
            ]
        );
        
        if($pMode == self::URL_ABSOLUTE || $pMode == self::SHEMA_RELATIVE)
        {
            $base = $this->_request->getBaseURL();
            $url = rtrim($base, '/') . $url;
            
            $secureSheme = $this->_request->getScheme(true);
            $unSecureSheme = $this->_request->getScheme(false);

            if($pMode == self::SHEMA_RELATIVE)
            {
                $replace = '//';
            }
            else
            {
                if($pRoute->hasOption(Route::SECURE, false))
                {
                    $replace = $pRoute->getOption(Route::SECURE) ? $secureSheme : $unSecureSheme;
                }
                else
                {
                    $replace = $this->_request->isSecure() ? $secureSheme : $unSecureSheme;
                }
            }

            $url = preg_replace(
                '/^(' . preg_quote($secureSheme, '/') . '|' . preg_quote($unSecureSheme, '/') . ')/',
                $replace,
                $url
            );
        }
        
        return $url;
    }
}
