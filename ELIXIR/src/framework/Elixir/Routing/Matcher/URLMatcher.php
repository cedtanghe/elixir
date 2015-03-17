<?php

namespace Elixir\Routing\Matcher;

use Elixir\HTTP\Request;
use Elixir\Routing\Collection;
use Elixir\Routing\Matcher\MatcherInterface;
use Elixir\Routing\Matcher\RouteMatch;
use Elixir\Routing\Route;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class URLMatcher implements MatcherInterface
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
     * @var array 
     */
    protected $_references = [];

    /**
     * @param Request $pRequest
     */
    public function __construct(Request $pRequest = null)
    {
        if(null !== $pRequest)
        {
            $this->setRequest($pRequest);
        }
    }
    
    /**
     * @see MatcherInterface::setRequest()
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
     * @see MatcherInterface::match()
     * @throws \RuntimeException
     */
    public function match(Collection $pCollection, $pURL)
    {
        if(null === $this->_request)
        {
            throw new \RuntimeException('Request class is not defined.');
        }
        
        $pCollection->sort();
        
        foreach($pCollection->gets() as $key => $value)
        {
            if($value->hasOption(Route::METHOD))
            {
                $methods = array_map(function($pElement)
                {
                    return strtoupper($pElement);
                }, 
                $value->getOption(Route::METHOD));
                
                if(!in_array($this->_request->getRequestMethod('GET'), $methods))
                {
                    continue;
                }
            }
            
            if($value->hasOption(Route::SECURE))
            {
                if($value->getOption(Route::SECURE) !== $this->_request->isSecure())
                {
                    continue;
                }
            }
            
            $pattern = $this->compile($value);
            
            if(preg_match($pattern, $pURL, $matches))
            {
                $routeMatch = $this->createRouteMatch($key, $value, $matches);
                
                if($value->hasOption(Route::ASSERT))
                {
                    $call = $value->getOption(Route::ASSERT);
                    
                    if(false === call_user_func_array($call, [$routeMatch]))
                    {
                        continue;
                    }
                }
                
                if($value->hasOption(Route::MATCHED_FILTER))
                {
                    $call = $value->getOption(Route::MATCHED_FILTER);
                    call_user_func_array($call, [$routeMatch]);
                }
                
                return $routeMatch;
            }
        }
        
        return null;
    }
    
    /**
     * @param Route $pRoute
     * @return string
     */
    protected function compile(Route $pRoute)
    {
        $pattern = $pRoute->getPattern();
        
        foreach($pRoute->getOptions() as $key => $value)
        {
            switch($key)
            {
                case Route::SECURE:
                case Route::METHOD:
                case Route::ASSERT:
                case Route::MATCHED_FILTER:
                case Route::GENERATE_FILTER:
                case Route::PREFIX:
                case Route::SUFFIX:
                    continue 2;
                break;
                case Route::REPLACEMENTS:
                    foreach($value as $k => $v)
                    {
                        $pattern = str_replace('%' . $k . '%', $v, $pattern);
                    }
                    continue 2;
                break;
                case Route::ATTRIBUTES:
                    $mask = '{' . $key . '}';
                    
                    if(false === strpos($pattern, $mask))
                    {
                        $pattern .= '{' . $key . '}';
                    }
                break;
            }
            
            $pattern = str_replace('{' . $key . '}', '(?P<' . $this->protect($key) . '>' . $value . ')', $pattern);
        }
        
        return self::REGEX_SEPARATOR . '^' . $pattern . '$' . self::REGEX_SEPARATOR;
    }
    
    /**
     * @param string $pValue
     * @return string
     */
    protected function protect($pValue)
    {
        $key = str_replace(str_split('.\+*?[^]$(){}=!<>|:-%'), '', $pValue);
        $this->_references[$key] = $pValue;
        
        return $key;
    }

    /**
     * @param string $pName
     * @param Route $pRoute
     * @param array $pMatches
     * @return RouteMatch
     */
    protected function createRouteMatch($pName, Route $pRoute, array $pMatches)
    {
        $match = new RouteMatch($pName, $pRoute->getParameters());
        
        foreach($pMatches as $key => $value)
        {
            if(isset($this->_references[$key]) && !empty($value))
            {
                $match->set($this->_references[$key], $value);
            }
        }
        
        return $match;
    }
}
