<?php

namespace Elixir\DB;

use Elixir\DB\Query\QueryInterface;
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
     * @var QueryInterface|string 
     */
    protected $query;

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
                'query' => null,
                'values' => [],
                'time' => 0
            ], 
            $params
        );
        
        $this->query = $params['query'];
        $this->values = $params['values'];
        $this->time = $params['time'];
    }
    
    /**
     * @return QueryInterface|string
     */
    public function getQuery() 
    {
        return $this->query;
    }
    
    /**
     * @param QueryInterface|string $value
     */
    public function setQuery($value) 
    {
        $this->query = $value;
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
