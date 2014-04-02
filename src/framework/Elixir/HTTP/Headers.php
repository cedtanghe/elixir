<?php

namespace Elixir\HTTP;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Headers
{
    /**
     * @param array $pServerDataFailback
     * @return Headers|null
     */
    public static function fromApacheRequestHeaders($pServerDataFailback = null)
    {
        if(function_exists('apache_request_headers'))
        {
            $apacheHeaders = apache_request_headers();
            $headers = new static();

            foreach($apacheHeaders as $key => $value) 
            {
                $headers->set($key, $value);
            }
        }
        else if(null !== $pServerDataFailback)
        {
            $headers = new static();
            
            foreach($pServerDataFailback as $key => $value) 
            { 
                if ('HTTP_' == substr($key, 0, 5))
                { 
                    $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5))))); 
                    $headers->set($key, $value);
                } 
            } 
        }
        
        return isset($headers) ? $headers : null;
    }

    /**
     * @var array 
     */
    protected $_headers = array();
    
    /**
     * @var array 
     */
    protected $_cacheControl = array();
    
    /**
     * @var array 
     */
    protected $_cookies = array();

    /**
     * @param string $pKey
     * @return boolean
     */
    public function has($pKey)
    {
        if($pKey == 'Cache-Control')
        {
            return count($this->_cacheControl) > 0;
        }
        
        return isset($this->_headers[$pKey]);
    }
    
    /**
     * @param string $pKey
     * @param string|null $pValue
     * @param boolean $pReplace
     */
    public function set($pKey, $pValue = null, $pReplace = true)
    {
        if(null === $pValue)
        {
            $parsed = explode(':', $pKey, 2);
            
            if(count($parsed) > 1)
            {
                $pKey = trim($parsed[0]);
                $pValue = trim($parsed[1]);
            }
        }
        
        if($pKey == 'Set-Cookie')
        {
            $this->setCookie(Cookie::fromString($pValue));
            return;
        }
        else if(in_array($pKey, array('Expires', 'Last-Modified')) && $pValue instanceof \DateTime)
        {
            $date = new \DateTime('@' . $pValue->getTimestamp(), new \DateTimeZone('GMT'));
            $pValue = $date->format('D, d-M-Y H:i:s \G\M\T');
        }
        else if($pKey == 'Cache-Control')
        {
            if($pReplace)
            {
                $this->setCacheControlDirectives((array)$pValue);
            }
            else
            {
                $this->addCacheControlDirective($pKey, $pValue);
            }
            
            return;
        }
        
        if($pReplace || !isset($this->_headers[$pKey]))
        {
            $this->_headers[$pKey] = $pValue;
        }
        else
        {
            $this->_headers[$pKey] = (array)$this->_headers[$pKey];
            $this->_headers[$pKey][] = $pValue;
        }
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     */
    public function get($pKey, $pDefault = null)
    {
        if($this->has($pKey))
        {
            if($pKey == 'Cache-Control')
            {
                return $this->parseCacheControl();
            }
            
            $header = $this->_headers[$pKey];
            return count($header) > 0 ? $header : $header[0];
        }
        
        if(is_callable($pDefault))
        {
            return call_user_func($pDefault);
        }
        
        return $pDefault;
    }

    /**
     * @param string $pKey
     */
    public function remove($pKey)
    {
        if($pKey == 'Cache-Control')
        {
            $this->setCacheControlDirectives(array());
            return;
        }
        
        unset($this->_headers[$pKey]);
    }
    
    /**
     * @return array 
     */
    public function gets()
    {
        $this->_headers['Cache-Control'] = $this->parseCacheControl();
        return $this->_headers;
    }
    
    /**
     * @param array $pData
     */
    public function sets(array $pData)
    {
        $this->_headers = array();
        
        foreach($pData as $key => $value)
        {
            if(is_int($key))
            {
                $key = $value;
                $value = null;
            }
            
            $this->set($key, $value);
        }
    }
    
    /**
     * @param string $pKey
     * @return boolean
     */
    public function hasCacheControlDirective($pKey)
    {
        return isset($this->_cacheControl[$pKey]);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function getCacheControlDirective($pKey, $pDefault)
    {
        if($this->hasCacheControlDirective($pKey))
        {
            return $this->_cacheControl[$pKey];
        }
        
        return $pDefault;
    }
    
    /**
     * @param string $pKey
     * @param mixed $pValue
     */
    public function addCacheControlDirective($pKey, $pValue = true)
    {
        if(null === $pValue)
        {
            $pValue = true;
        }
        
        if(false === $pValue)
        {
            $this->removeCacheControlDirective($pKey);
            return;
        }
        
        $this->_cacheControl[$pKey] = $pValue;
    }
    
    /**
     * @param string $pKey
     */
    public function removeCacheControlDirective($pKey)
    {
        unset($this->_cacheControl[$pKey]);
    }
    
    /**
     * @return array 
     */
    public function getCacheControlDirectives()
    {
        return $this->_cacheControl;
    }
    
    /**
     * @param array $pData
     */
    public function setCacheControlDirectives(array $pData)
    {
        $this->_cacheControl = array();
        
        foreach($pData as $key => $value)
        {
            if(is_int($key))
            {
                $key = $value;
                $value = null;
            }
            
            $this->addCacheControlDirective($key, $value);
        }
    }

    /**
     * @param string $pName
     * @param string $pPath
     * @param string $pDomain
     * @return boolean
     */
    public function hasCookie($pName, $pPath = '/', $pDomain = '')
    {
        return isset($this->_cookies[$pDomain][$pPath][$pName]);
    }
    
    /**
     * @param Cookie $pCookie
     */
    public function setCookie(Cookie $pCookie)
    {
        $this->_cookies[$pCookie->getDomain()][$pCookie->getPath()][$pCookie->getName()] = $pCookie;
    }
    
    /**
     * @param string $pName
     * @param string $pPath
     * @param string $pDomain
     */
    public function removeCookie($pName, $pPath = '/', $pDomain = '')
    {
        unset($this->_cookies[$pDomain][$pPath][$pName]);
        
        if(empty($this->_cookies[$pDomain][$pPath]))
        {
            unset($this->_cookies[$pDomain][$pPath]);
            
            if(empty($this->_cookies[$pDomain]))
            {
                unset($this->_cookies[$pDomain]);
            }
        }
    }
    
    /**
     * @param string $pName
     * @param string $pPath
     * @param string $pDomain
     */
    public function clearCookie($pName, $pPath = '/', $pDomain = '')
    {
        $this->setCookie(new Cookie($pName, '', time() - 3600, $pPath, $pDomain));
    }
    
    /**
     * @param boolean $pWithConfiguration
     * @return array
     */
    public function getCookies($pWithConfiguration = false)
    {
        if($pWithConfiguration)
        {
            return $this->_cookies;
        }

        $cookies = array();

        foreach($this->_cookies as $domain)
        {
            foreach($domain as $path)
            {
                foreach($path as $cookie)
                {
                    $cookies[] = $cookie;
                }
            }
        }

        return $cookies;
    }
    
    /**
     * @param array $pData
     */
    public function setCookies(array $pData)
    {
        $this->_cookies = array();
        
        foreach($pData as $cookie)
        {
            $this->setCookie($cookie);
        }
    }
    
    /**
     * @return array
     */
    protected function getSortedHeaders()
    {
        $this->_headers['Cache-Control'] = $this->parseCacheControl();
        ksort($this->_headers);
        
        $headers = array();
        
        foreach($this->_headers as $key => $value)
        {
            if(preg_match('/^HTTP\/1\.(0|1) \d{3}.*$/', $key))
            {
                array_unshift($headers, $key);
            }
            else
            {
                if(is_array($value))
                {
                    foreach($value as $v)
                    {
                        $headers[] = $key . ': ' . $v;
                    }
                }
                else
                {
                    $headers[] = $key . ': ' . $value;
                }
            }
        }
        
        return $headers;
    }

    /**
     * @return string
     */
    protected function parseCacheControl()
    {
        if(!$this->has('Cache-Control') && !$this->has('Etag', false) && 
           !$this->has('Last-Modified') && !$this->has('Expires'))
        {
            return 'no-cache'; // Or 'no-cache, must-revalidate' ?
        }
        
        if(!$this->has('Cache-Control', false))
        {
            return 'private, must-revalidate';
        }
        
        ksort($this->_cacheControl);
        $control = array();
        
        foreach($this->_cacheControl as $key => $value)
        {
            if(true === $value)
            {
                $control[] = $key;
            }
            else if(!empty($value))
            {
                if(is_int($key))
                {
                    $control[] = $value;
                }
                else
                {
                    $control[] = sprintf('%s=%s', $key, $value);
                }
            }
        }
        
        $control = implode(', ', $control);
        
        if(!$this->getCacheControlDirective('public', false) && 
           !$this->getCacheControlDirective('private', false) && 
           !$this->getCacheControlDirective('s-maxage', false))
        {
            return $control . ', private';
        }
        
        return $control;
    }

    /**
     * @return boolean
     */
    public function send()
    {
        if(headers_sent())
        {
            return false;
        }
        
        foreach($this->getCookies() as $cookie)
        {
            if(!$cookie->send())
            {
                return false;
            }
        }
        
        foreach($this->getSortedHeaders() as $header)
        {
            header($header);
        }
        
        return true;
    }
    
    /**
     * @return string
     */
    public function __toString() 
    {
        $return = implode($this->getSortedHeaders(), "\r\n");
        
        foreach($this->getCookies() as $cookie)
        {
            $result = $cookie->toString();
            
            if(is_array($result))
            {
                foreach($result as $c)
                {
                    $return .= "\r\n" . 'Set-Cookie: ' . $c;
                }
            }
            else
            {
                $return .= "\r\n" . 'Set-Cookie: ' . $result;
            }
        }
        
        return $return;
    }
}