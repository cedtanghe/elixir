<?php

namespace Elixir\Routing;

use Elixir\Routing\Route;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Collection
{
    /**
     * @var array
     */
    protected $_routes = array();
    
    /**
     * @var integer
     */
    protected $_serial = 0;
    
    /**
     * @var boolean
     */
    protected $_sorted = false;
    
    /**
     * @param string $pName
     * @return boolean
     */
    public function has($pName)
    {
        return array_key_exists($pName, $this->_routes);
    }
    
    /**
     * @param string $pName
     * @param mixed $pDefault
     * @return mixed
     */
    public function get($pName, $pDefault = null)
    {
        if($this->has($pName))
        {
            return $this->_routes[$pName]['route'];
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @param string $pName
     * @param Route $pRoute
     * @param integer $pPriority
     */
    public function add($pName, Route $pRoute, $pPriority = 0)
    {
        $this->_sorted = false;
        $this->_routes[$pName] = array('route' => $pRoute,
                                       'priority' => $pPriority, 
                                       'serial' => $this->_serial++);
    }
    
    /**
     * @param string $pName
     */
    public function remove($pName)
    {
        unset($this->_routes[$pName]);
    }
    
    /**
     * @param boolean $pWithInfos
     * @return array
     */
    public function gets($pWithInfos = false)
    {
        $routes = array();

        foreach($this->_routes as $key => $value)
        {
            $routes[$key] = $pWithInfos ? $value : $value['route'];
        }

        return $routes;
    }
    
    /**
     * @param array $pData
     */
    public function sets(array $pData)
    {
        $this->_routes = array();
        $this->_serial = 0;
        
        foreach($pData as $name => $route)
        {
            $priority = 0;
            
            if(is_array($route))
            {
                $route = $route['route'];
                
                if(isset($route['priority']))
                {
                    $priority = $route['priority'];
                }
            }
            
            $this->add($name, $route, $priority);
        }
    }
    
    /**
     * @param array $p1
     * @param array $p2
     * @return integer
     */
    protected function compare(array $p1, array $p2)
    {
        if ($p1['priority'] == $p2['priority']) 
        {
            return ($p1['serial'] < $p2['serial']) ? -1 : 1;
        }
        
        return ($p1['priority'] > $p2['priority']) ? -1 : 1;
    }
    
    /**
     * @internal
     */
    public function sort()
    {
        if(!$this->_sorted)
        {
            uasort($this->_routes, array($this, 'compare'));
            $this->_sorted = true;
        }
    }
    
    /**
     * @param Collection|array $pData
     */
    public function merge($pData)
    {
        if($pData instanceof self)
        {
            $pData = $pData->gets(true);
        }
        
        if(count($pData) > 0)
        {
            $this->_sorted = false;
        
            foreach($pData as $name => $data)
            {
                $priority = 0;
                $serial = 0;
            
                if(is_array($data))
                {
                    $route = $data['route'];

                    if(isset($data['priority']))
                    {
                        $priority = $data['priority'];
                    }
                    
                    if(isset($data['serial']))
                    {
                        $serial = $data['serial'];
                    }
                }
                
                $this->_routes[$name] = array(
                    'route' => $route,
                    'priority' => $priority, 
                    'serial' => ($this->_serial++) + $serial
                );
            }
        }
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     */
    public function __call($pMethod, $pArguments)
    {
        foreach($this->_routes as $data)
        {
            call_user_func_array(array($data['route'], $pMethod), $pArguments);
        }
    }
}