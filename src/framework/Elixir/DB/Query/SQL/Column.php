<?php

namespace Elixir\DB\SQL;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Column
{
    /**
     * @var string
     */
    const TINYINT = 'TINYINT';
    
    /**
     * @var string
     */
    const SMALLINT = 'SMALLINT';
    
    /**
     * @var string
     */
    const MEDIUMINT = 'MEDIUMINT';
    
    /**
     * @var string
     */
    const INT = 'INT';
    
    /**
     * @var string
     */
    const INTEGER = 'INTEGER';
    
    /**
     * @var string
     */
    const BIGINT = 'BIGINT';
    
    /**
     * @var string
     */
    const REAL = 'REAL';
    
    /**
     * @var string
     */
    const DOUBLE = 'DOUBLE';
    
    /**
     * @var string
     */
    const FLOAT = 'FLOAT';
    
    /**
     * @var string
     */
    const DECIMAL = 'DECIMAL';
    
    /**
     * @var string
     */
    const NUMERIC = 'NUMERIC';
    
    /**
     * @var string
     */
    const DATE = 'DATE';
    
    /**
     * @var string
     */
    const TIME = 'TIME';
    
    /**
     * @var string
     */
    const TIMESTAMP = 'TIMESTAMP';
    
    /**
     * @var string
     */
    const DATETIME = 'DATETIME';
    
    /**
     * @var string
     */
    const CHAR = 'CHAR';
    
    /**
     * @var string
     */
    const VARCHAR = 'VARCHAR';
    
    /**
     * @var string
     */
    const TINYBLOB = 'TINYBLOB';
    
    /**
     * @var string
     */
    const BLOB = 'BLOB';
    
    /**
     * @var string
     */
    const MEDIUMBLOB = 'MEDIUMBLOB';
    
    /**
     * @var string
     */
    const LONGBLOB = 'LONGBLOB';
    
    /**
     * @var string
     */
    const TINYTEXT = 'TINYTEXT';
    
    /**
     * @var string
     */
    const TEXT = 'TEXT';
    
    /**
     * @var string
     */
    const MEDIUMTEXT = 'MEDIUMTEXT';
    
    /**
     * @var string
     */
    const LONGTEXT = 'LONGTEXT';
    
    /**
     * @var string
     */
    const ENUM = 'ENUM';
    
    /**
     * @var string
     */
    const SET = 'SET';
    
    /**
     * @var string
     */
    const GEOMETRY = 'GEOMETRY';
    
    /**
     * @var string
     */
    const POINT = 'POINT';
    
    /**
     * @var string
     */
    const LINESTRING = 'LINESTRING';
    
    /**
     * @var string
     */
    const POLYGON = 'POLYGON';
    
    /**
     * @var string
     */
    const MULTIPOINT = 'MULTIPOINT';
    
    /**
     * @var string
     */
    const MULTILINESTRING = 'MULTILINESTRING';
    
    /**
     * @var string
     */
    const MULTIPOLYGON = 'MULTIPOLYGON';
    
    /**
     * @var string
     */
    const GEOMETRYCOLLECTION = 'GEOMETRYCOLLECTION';
    
    /**
     * @var string
     */
    const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';
    
    /**
     * @var string
     */
    const BINARY = 'BINARY';
    
    /**
     * @var string
     */
    const UNSIGNED = 'UNSIGNED';
    
    /**
     * @var string
     */
    const UNSIGNED_ZEROFILL = 'UNSIGNED_ZEROFILL';
    
    /**
     * @var string
     */
    const UPDATE_CURRENT_TIMESTAMP = 'ON UPDATE CURRENT_TIMESTAMP';
    
    /**
     * @var string
     */
    protected $_name;
    
    /**
     * @var string
     */
    protected $_type;
    
    /**
     * @var mixed
     */
    protected $_value;
    
    /**
     * @var mixed
     */
    protected $_default;
    
    /**
     * @var string
     */
    protected $_collation = null;
    
    /**
     * @var string
     */
    protected $_attribute;

    /**
     * @var boolean
     */
    protected $_nullable = false;
    
    /**
     * @var boolean 
     */
    protected $_autoIncrement = false;
    
    /**
     * @var string
     */
    protected $_comment;

    /**
     * @param string $pName
     * @param string $pType
     */
    public function __construct($pName = null, $pType = null)
    {
        if(null !== $pName)
        {
            $this->setName($pName);
        }
        
        if(null !== $pType)
        {
            $this->setType($pType);
        }
    }
    
    /**
     * @param string $pValue
     * @return Column
     */
    public function setName($pValue)
    {
        $this->_name = $pValue;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * @param string $pValue
     * @return Column
     */
    public function setType($pValue)
    {
        $this->_type = $pValue;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }
    
    /**
     * @param mixed $pValue
     * @return Column
     */
    public function setValue($pValue)
    {
        $this->_value = $pValue;
        return $this;
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
     * @return Column
     */
    public function setDefault($pValue)
    {
        $this->_default = $pValue;
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->_default;
    }
    
    /**
     * @param string $pValue
     * @return Column
     */
    public function setCollating($pValue)
    {
        $this->_collation = $pValue;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getCollating()
    {
        return $this->_collation;
    }
    
    /**
     * @param string $pValue
     * @return Column
     */
    public function setAttribute($pValue)
    {
        $this->_attribute = $pValue;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getAttribute()
    {
        return $this->_attribute;
    }
    
    /**
     * @param boolean $pValue
     * @return Column
     */
    public function setNullable($pValue)
    {
        $this->_nullable = $pValue;
        return $this;
    }
    
    /**
     * @return boolean
     */
    public function isNullable()
    {
        return $this->_nullable;
    }
    
    /**
     * @param boolean $pValue
     * @return Column
     */
    public function setAutoIncrement($pValue)
    {
        $this->_autoIncrement = $pValue;
        return $this;
    }
    
    /**
     * @return boolean
     */
    public function isAutoIncrement()
    {
        return $this->_autoIncrement;
    }
    
    /**
     * @param string $pValue
     * @return Column
     */
    public function setComment($pValue)
    {
        $this->_comment = $pValue;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getComment()
    {
        return $this->_comment;
    }
}
