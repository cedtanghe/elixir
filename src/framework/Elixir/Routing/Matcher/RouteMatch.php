<?php

namespace Elixir\Routing\Matcher;

use Elixir\Routing\Route;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class RouteMatch
{
    /**
     * @var array 
     */
    protected $_params = [];
    
    /**
     * @var string
     */
    protected $_routeName;

    /**
     * @param string $pRouteName
     * @param array $pParams
     */
    public function __construct($pRouteName, array $pParams = [])
    {
        $this->_routeName = $pRouteName;
        $this->sets($pParams);
    }
    
    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->_routeName;
    }

    /**
     * @param string $pKey
     * @return boolean
     */
    public function has($pKey)
    {
        return array_key_exists($pKey, $this->_params);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function get($pKey, $pDefault = null)
    {
        if($this->has($pKey))
        {
            return $this->_params[$pKey];
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @param string $pKey
     * @param mixed $pValue
     */
    public function set($pKey, $pValue)
    {
        switch($pKey)
        {
            case Route::ATTRIBUTES:
                $params = explode('/', trim($pValue, '/'));
                $len = count($params);

                if($len > 0)
                {
                    if($len % 2 == 1)
                    {
                        $params[] = '';
                        $len++;
                    }

                    for($i = 0; $i < $len; ++$i)
                    {
                        if(preg_match('/^[a-z0-9-_]+$/i', $params[$i]))
                        {
                            if(!$this->has($params[$i]))
                            {
                                $this->set($params[$i], rawurldecode($params[++$i]));
                            }
                        }
                    }
                }
            break;
            default:
                $this->_params[$pKey] = trim($pValue, '/');
            break;
        }
    }
    
    /**
     * @param string $pKey
     */
    public function remove($pKey)
    {
        unset($this->_params[$pKey]);
    }
    
    /**
     * @return array
     */
    public function gets()
    {
        return $this->_params;
    }
    
    /**
     * @param array $pData
     */
    public function sets(array $pData)
    {
        $this->_params = [];
        
        foreach($pData as $key => $value)
        {
            $this->set($key, $value);
        }
    }
}
