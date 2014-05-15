<?php

namespace Elixir\DB;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class DBEvent extends Event
{
    /**
     * @var string
     */
    const PRE_QUERY = 'pre_query';
    
    /**
     * @var string
     */
    const QUERY = 'query';
    
    /**
     * @var string 
     */
    protected $_SQL;
    
    /**
     * @var array 
     */
    protected $_values;
    
    /**
     * @var float 
     */
    protected $_time;
    
    /**
     * @see Event::__contruct()
     * @param string $pSQL
     * @param array $pValues
     * @param float $pTime
     */
    public function __construct($pType, $pSQL = null, array $pValues = [], $pTime = 0) 
    {
        parent::__construct($pType);
        
        $this->_SQL = $pSQL;
        $this->_values = $pValues;
        $this->_time = $pTime;
    }
    
    /**
     * @return string
     */
    public function getSQL()
    {
        return $this->_SQL;
    }
    
    /**
     * @return array
     */
    public function getValues()
    {
        return $this->_values;
    }
    
    /**
     * @return float
     */
    public function getTime()
    {
        return $this->_time;
    }
}
