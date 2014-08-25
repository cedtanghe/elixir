<?php

namespace Elixir\HTTP;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Cookie 
{
    /**
     * @param string $pValue
     * @return Cookie
     */
    public static function fromString($pValue)
    {
        $segments = explode(';', $pValue);
        $part = explode('=', array_shift($segments));
        
        $data = [
            'name' => trim($part[0]), 
            'value' => rawurldecode(trim($part[1])), 
            'expires' => 0, 
            'path' => '', 
            'domain' => '', 
            'secure' => false, 
            'httponly' => false
        ];
        
        foreach($segments as $segment)
        {
            $part = explode('=', $segment);
            $key = trim(strtolower($part[0]));
            
            switch($key)
            {
                case 'expires':
                    $date = \DateTime::createFromFormat('D, d-M-Y H:i:s \G\M\T', trim($part[1]), new \DateTimeZone('GMT'));
                    $data[$key] = false !== $date ? $date->getTimestamp() : 0;
                break;
                case 'path':
                case 'domain':
                    $data[$key] = trim($part[1]);
                break;
                case 'secure':
                case 'httponly':
                    $data[$key] = true;
                break;
            }
        }
        
        return new static(
            $data['name'],
            $data['value'],
            $data['expires'],
            $data['path'],
            $data['domain'],
            $data['secure'],
            $data['httponly']
        );
    }
    
    /**
     * @var string 
     */
    protected $_name;
    
    /**
     * @var mixed 
     */
    protected $_value;
    
    /**
     * @var integer 
     */
    protected $_expires;
    
    /**
     * @var string 
     */
    protected $_path;
    
    /**
     * @var string 
     */
    protected $_domain;
    
    /**
     * @var boolean 
     */
    protected $_secure;
    
    /**
     * @var boolean 
     */
    protected $_HTTPOnly;
    
    /**
     * @param string $pName
     * @param mixed $pValue
     * @param integer|string|\DateTime $pExpires
     * @param string $pPath
     * @param string $pDomain
     * @param boolean $pSecure
     * @param boolean $pHTTPOnly
     */
    public function __construct($pName, 
                                $pValue = '', 
                                $pExpires = 0, 
                                $pPath = '/', 
                                $pDomain = '', 
                                $pSecure = false, 
                                $pHTTPOnly = false) 
    {
        $this->_name = $pName;
        
        $this->setValue($pValue);
        $this->setExpires($pExpires);
        $this->setPath($pPath);
        $this->setDomain($pDomain);
        $this->setSecure($pSecure);
        $this->setHTTPOnly($pHTTPOnly);
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }
    
    /**
     * @param mixed $pValue
     */
    public function setValue($pValue)
    {
        $this->_value = $pValue;
    }
    
    /**
     * @return integer
     */
    public function getExpires()
    {
        return $this->_expires;
    }
    
    /**
     * @param integer|string|\DateTime $pValue
     */
    public function setExpires($pValue)
    {
        if($pValue instanceof \DateTime)
        {
            $pValue = $pValue->format('U');
        }
        else if(!is_numeric($pValue))
        {
            $pValue = strtotime($pValue);
        }
        
        if(empty($pValue))
        {
            $pValue = 0;
        }
        
        $this->_expires = $pValue;
    }
    
    /**
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }
    
    /**
     * @param string $pValue
     */
    public function setPath($pValue)
    {
        if(empty($pValue))
        {
           $pValue = '/'; 
        }
        
        $this->_path = $pValue;
    }
    
    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->_domain;
    }
    
    /**
     * @param string $pValue
     */
    public function setDomain($pValue)
    {
        if (!empty($pValue))
        {
            if (strtolower(substr($pValue, 0, 4)) == 'www.')
            {
                $pValue = substr($pValue, 4);
            }
            
            $pValue = '.' . $pValue;

            $port = strpos($pValue, ':');
            
            if (false !== $port)  
            {
                $pValue = substr($pValue, 0, $port);
            }
        }
        
        $this->_domain = $pValue;
    }
    
    /**
     * @return boolean
     */
    public function isSecure()
    {
        return $this->_secure;
    }
    
    /**
     * @param boolean $pValue
     */
    public function setSecure($pValue)
    {
        $this->_secure = $pValue;
    }
    
    /**
     * @return boolean
     */
    public function isHTTPOnly()
    {
        return $this->_HTTPOnly;
    }
    
    /**
     * @param boolean $pValue
     */
    public function setHTTPOnly($pValue)
    {
        $this->_HTTPOnly = $pValue;
    }
    
    /**
     * @return boolean
     */
    public function send()
    {
        if(is_array($this->_value))
        {
            foreach($this->_value as $key => $value)
            {
                if(true !== setcookie($this->_name . '[' .  $key . ']',
                                      (string)$value,
                                      $this->_expires,
                                      $this->_path,
                                      $this->_domain,
                                      $this->_secure,
                                      $this->_HTTPOnly))
                {
                    return false;
                }
            }
        }
        else
        {
            return (bool)setcookie(
                $this->_name,
                $this->_value,
                $this->_expires,
                $this->_path,
                $this->_domain,
                $this->_secure,
                $this->_HTTPOnly
            );
        }
        
        return true;
    }
    
    /**
     * @return string|array
     */
    public function toString()
    {
        $name = rawurlencode($this->_name);
        $cookies = [];
        
        if(is_array($this->_value))
        {
            foreach($pValue as $key => $value)
            {
                $cookies[$name . '[' .  $key . ']'] = rawurlencode($value);
            }
        }
        else
        {
            $cookies[$name] = rawurlencode($this->_value);
        }
        
        $return = [];
        
        foreach($cookies as $key => $value)
        {
            $cookie = $key . '=';
            
            if('' !== (string)$value)
            {
                $cookie .= $value;
                
                if($this->_expires != 0)
                {
                    $date = new \DateTime('@' . $this->_expires, new \DateTimeZone('GMT'));
                    $cookie .= '; expires=' . $date->format('D, d-M-Y H:i:s \G\M\T');
                }
            }
            else
            {
                $date = new \DateTime('@' . (time() - 3600), new \DateTimeZone('GMT'));
                $cookie .= 'null; expires=' . $date->format('D, d-M-Y H:i:s \G\M\T');
            }
            
            if(!empty($this->_path))
            {
                $cookie .= '; path=' . $this->_path;
            }
            
            if(!empty($this->_domain))
            {
                $cookie .= '; domain=' . $this->_domain;
            }
            
            if($this->_secure)
            {
                $cookie .= '; secure';
            }
            
            if($this->_HTTPOnly)
            {
                $cookie .= '; httponly';
            }
            
            $return[] = $cookie;
        }
        
        return count($return) > 0 ? $return[0] : $return;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function __toString() 
    {
        if(is_array($this->_value))
        {
            throw new \RuntimeException(
                'The cookie contains multiple values' .
                ' and therefore can not be made as a single string.'
            );
        }
        
        return $this->toString();
    }
}
