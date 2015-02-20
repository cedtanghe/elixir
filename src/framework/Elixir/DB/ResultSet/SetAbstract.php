<?php

namespace Elixir\DB\ResultSet;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
abstract class SetAbstract 
{
    /**
     * @var integer
     */
    const FETCH_ASSOC = 2;
    
    /**
     * @var integer
     */
    const FETCH_NUM = 3;

    /**
     * @var integer
     */
    const FETCH_BOTH = 4;
    
    /**
     * @var integer
     */
    const FETCH_OBJ = 5;

    /**
     * @var integer
     */
    const FETCH_DEFAULT = 19;

    /**
     * @var mixed
     */
    protected $resource;

    /**
     * @param mixed $resource
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param string $fetchStyle
     * @return mixed;
     */
    abstract public function fetch($fetchStyle = self::FETCH_DEFAULT);

    /**
     * @param string $fetchStyle
     * @return array;
     */
    abstract public function fetchAll($fetchStyle = self::FETCH_ASSOC);

    /**
     * @param integer $column
     * @return mixed;
     */
    abstract public function fetchColumn($column = 0);

    /**
     * @param string $className
     * @param array $args
     * @return mixed;
     */
    abstract public function fetchObject($className = 'stdClass', array $args = []);

    /**
     * @param string $pClassName
     * @param array $pArgs
     * @return mixed;
     */
    abstract public function fetchAssoc();

    /**
     * @return integer
     */
    abstract public function rowCount();
}
