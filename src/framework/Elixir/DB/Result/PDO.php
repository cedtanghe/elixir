<?php

namespace Elixir\DB\Result;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class PDO extends SetAbstract
{
    /**
     * @param string|integer $pFetchStyle
     * @return string|integer
     */
    protected function convert($pFetchStyle)
    {
        switch($pFetchStyle)
        {
            case self::FETCH_ASSOC:
                return \PDO::FETCH_ASSOC;
            break;
            case self::FETCH_OBJ:
                return \PDO::FETCH_OBJ;
            break;
            case self::FETCH_NUM:
                return \PDO::FETCH_NUM;
            break;
            case self::FETCH_BOTH:
                return \PDO::FETCH_BOTH;
            break;
            case self::FETCH_DEFAULT:
                return \PDO::ATTR_DEFAULT_FETCH_MODE;
            break;
        }
        
        return $pFetchStyle;
    }
    
    /**
     * @see SetAbstract::fetch()
     */
    public function fetch($pFetchStyle = self::FETCH_DEFAULT)
    {
        if(func_num_args() <= 1)
        {
            return $this->_resource->fetch($this->convert($pFetchStyle));
        }
        
        $args = func_get_args();
        $args[0] = $this->convert($args[0]);
        
        return call_user_func_array(array($this->_resource, 'fetch'), $args);
    }
    
    /**
     * @see SetAbstract::fetchAll()
     */
    public function fetchAll($pFetchStyle = self::FETCH_ASSOC)
    {
        if(func_num_args() <= 1)
        {
            return $this->_resource->fetchAll($this->convert($pFetchStyle));
        }
        
        $args = func_get_args();
        $args[0] = $this->convert($args[0]);
        
        return call_user_func_array(array($this->_resource, 'fetchAll'), $args);
    }
    
    /**
     * @see SetAbstract::fetchColumn()
     */
    public function fetchColumn($pColumn = 0)
    {
        return $this->_resource->fetchColumn($pColumn);
    }
    
    /**
     * @see SetAbstract::fetchObject()
     */
    public function fetchObject($pClassName = 'stdClass', array $pArgs = array())
    {
        return $this->_resource->fetchObject($pClassName, $pArgs);
    }
    
    /**
     * @see SetAbstract::rowCount()
     */
    public function rowCount()
    {
        return $this->_resource->rowCount();
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     * @return mixed
     */
    public function __call($pMethod, $pArguments)
    {
        return call_user_func_array(array($this->_resource, $pMethod), $pArguments);
    }
}
