<?php

namespace Elixir\Routing\Generator;

use Elixir\HTTP\Request;
use Elixir\Routing\Generator\GeneratorInterface;
use Elixir\Routing\Route;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class URLGenerator implements GeneratorInterface
{
    /**
     * @var string
     */
    const REGEX_SEPARATOR = '`';
    
    /**
     * @var Request 
     */
    protected $_request;
    
    /**
     * @var boolean
     */
    protected $_strict = false;
    
    /**
     * @param Request $pRequest
     */
    public function __construct(Request $pRequest = null)
    {
        if(null !== $this->_request)
        {
            $this->setRequest($pRequest);
        }
    }
    
    /**
     * @see GeneratorInterface::setRequest()
     */
    public function setRequest(Request $pValue)
    {
        $this->_request = $pValue;
    }
    
    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    /**
     * @param boolean $pValue
     */
    public function setStrict($pValue)
    {
        $this->_strict = $pValue;
    }
    
    /**
     * @return boolean
     */
    public function isStrict()
    {
        return $this->_strict;
    }
    
    /**
     * @internal
     * @param array $pMatches
     * @return string
     */
    public function clean($pMatches)
    {
        return preg_replace_callback('/\((.*)\)\?/U', [$this, 'clean'], $pMatches[1]);
    }
    
    /**
     * @see GeneratorInterface::generate()
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
        
        if(count($query) > 0)
        {
            $query = '?' . http_build_query($query);
        }
        else
        {
            $query = '';
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
        
        $url = preg_replace('/\((\/+)\)\?/U', '', $url);
        $url = trim(preg_replace_callback('/\((.*)\)\?/U', [$this, 'clean'], $url), '/');
        $url = strtr(
            rawurlencode($url), 
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
        
        if(!empty($attributes))
        {
            $url .= $attributes;
        }
        
        if(!empty($query))
        {
            $url .= $query;
        }
        
        if($pMode == self::URL_ABSOLUTE || $pMode == self::SHEMA_RELATIVE)
        {
            $base = $this->_request->getBaseURL();
            $url = rtrim($base, '/') . '/' . ltrim($url, '/');
           
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