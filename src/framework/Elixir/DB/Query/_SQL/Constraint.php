<?php

namespace Elixir\DB\Query\SQL;

use Elixir\DB\Query\SQL\Column;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Constraint
{
    /**
     * @var string
     */
    const INDEX = 'INDEX';
    
    /**
     * @var string
     */
    const PRIMARY = 'PRIMARY';
    
    /**
     * @var string
     */
    const UNIQUE = 'UNIQUE';
    
    /**
     * @var string
     */
    const FULLTEXT = 'FULLTEXT';
    
    /**
     * @var string
     */
    const FOREIGN_KEY = 'FOREIGN KEY';
    
    /**
     * @var string
     */
    const REFERENCE_RESTRICT = 'RESTRICT';
    
    /**
     * @var string
     */
    const REFERENCE_CASCADE = 'CASCADE';
    
    /**
     * @var string
     */
    const REFERENCE_SET_NULL = 'SET NULL';
    
    /**
     * @var string
     */
    const REFERENCE_NO_ACTION = 'NO ACTION';
    
    /**
     * @var string
     */
    const REFERENCE_SET_DEFAULT = 'SET DEFAULT';
    
    /**
     * @var string
     */
    protected $_type;
    
    /**
     * @var string
     */
    protected $_name;

    /**
     * @var array
     */
    protected $_columns;
    
    /**
     * @var string
     */
    protected $_referenceTable;
    
    /**
     * @var string
     */
    protected $_referenceColumn;
    
    /**
     * @var string
     */
    protected $_onDeleteRule = self::REFERENCE_NO_ACTION;
    
    /**
     * @var string
     */
    protected $_onUpdateRule = self::REFERENCE_NO_ACTION;
    
    /**
     * @param string|array $pColumns
     * @param string $pType
     */
    public function __construct($pColumns = null, $pType = null) 
    {
        if(null !== $pColumns)
        {
            $this->setColumns((array)$pColumns);
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
     * @param string $pValue
     * @return Column
     */
    public function setName($pValue)
    {
        $this->_name = $pValue;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        if(null === $this->_name)
        {
            $this->_name = strtolower('fk_' . $this->_referenceTable . '_' . $this->_referenceColumn . '_' . current($this->getColumns()));
        }
        
        return $this->_name;
    }
    
    /**
     * @param array $pValue
     * @return Constraint
     */
    public function setColumns(array $pValue)
    {
        $this->_columns = [];
        
        foreach($pValue as $column)
        {
            $this->addColumn($column);
        }
        
        return $this;
    }
    
    /**
     * @param string|Column $pColumn
     * @return Constraint
     */
    public function addColumn($pColumn)
    {
        if($pColumn instanceof Column)
        {
            $pColumn = $pColumn->getName();
        }
        
        $this->_columns[] = $pColumn;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->_columns;
    }
    
    /**
     * @param string $pValue
     * @return Constraint
     */
    public function setReferenceTable($pValue)
    {
        $this->_referenceTable = $pValue;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getReferenceTable()
    {
        return $this->_referenceTable;
    }
    
    /**
     * @param string $pValue
     * @return Constraint
     */
    public function setReferenceColumn($pValue)
    {
        $this->_referenceColumn = $pValue;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getReferenceColumn()
    {
        return $this->_referenceColumn;
    }
    
    /**
     * @param string $pValue
     * @return Constraint
     */
    public function setOnDeleteRule($pValue)
    {
        $this->_onDeleteRule = $pValue;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getOnDeleteRule()
    {
        return $this->_onDeleteRule;
    }
    
    /**
     * @param string $pValue
     * @return Constraint
     */
    public function setOnUpdateRule($pValue)
    {
        $this->_onUpdateRule = $pValue;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getOnUpdateRule()
    {
        return $this->_onUpdateRule;
    }
}
