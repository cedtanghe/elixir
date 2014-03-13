<?php

namespace Elixir\DB\Result;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

abstract class SetAbstract
{
    /**
     * @var string
     */
    const FETCH_ASSOC = 'fetch_assoc';
    
    /**
     * @var string
     */
    const FETCH_OBJ = 'fetch_obj';
    
    /**
     * @var string
     */
    const FETCH_NUM = 'fetch_num';
    
    /**
     * @var string
     */
    const FETCH_BOTH = 'fetch_both';
    
    /**
     * @var string
     */
    const FETCH_DEFAULT = 'fetch_default';
  
    /**
     * @var mixed
     */
    protected $_resource;
    
    /**
     * @param mixed $pResource
     */
    public function __construct($pResource)
    {
        $this->_resource = $pResource;
    }
    
    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->_resource;
    }
    
    /**
     * @param string $pFetchStyle
     * @return mixed;
     */
    abstract public function fetch($pFetchStyle = self::FETCH_DEFAULT);
    
    /**
     * @param string $pFetchStyle
     * @return array;
     */
    abstract public function fetchAll($pFetchStyle = self::FETCH_ASSOC);
    
    /**
     * @param integer $pColumn
     * @return mixed;
     */
    abstract public function fetchColumn($pColumn = 0);
    
    /**
     * @param string $pClassName
     * @param array $pArgs
     * @return mixed;
     */
    abstract public function fetchObject($pClassName = 'stdClass', array $pArgs = array());
    
    /**
     * @return integer
     */
    abstract public function rowCount();
}
