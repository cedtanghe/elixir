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
    protected $SQL;

    /**
     * @var array 
     */
    protected $values;

    /**
     * @var float 
     */
    protected $time;

    /**
     * @see Event::__contruct()
     * @param array $params
     */
    public function __construct($type, array $params = []) 
    {
        parent::__construct($type);

        $params = array_merge(
            [
                'SQL' => null,
                'values' => [],
                'time' => 0
            ], 
            $params
        );
        
        $this->SQL = $params['SQL'];
        $this->values = $params['values'];
        $this->time = $params['time'];
    }
    
    /**
     * @return string
     */
    public function getSQL() 
    {
        return $this->SQL;
    }
    
    /**
     * @param string $value
     */
    public function setSQL($value) 
    {
        $this->SQL = $value;
    }

    /**
     * @return array
     */
    public function getValues() 
    {
        return $this->values;
    }
    
    /**
     * @param array $value
     */
    public function setValues(array $value) 
    {
        $this->values = $value;
    }
    
    /**
     * @return float
     */
    public function getTime() 
    {
        return $this->time;
    }
}
