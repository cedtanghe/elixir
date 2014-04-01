<?php

namespace Elixir\DB\SQL;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Insert extends SQLAbstract
{
    /**
     * @var string 
     */
    const VALUES_SET = 'set';
    
    /**
     * @var string 
     */
    const VALUES_ADD = 'add';
    
    /**
     * @var string 
     */
    protected $_table;
    
    /**
     * @var boolean 
     */
    protected $_raw = false;
    
    /**
     * @var array
     */
    protected $_columns = array();
    
    /**
     * @var string|array
     */
    protected $_values = null;
    
    /**
     * @param string $pTable
     */
    public function __construct($pTable = null) 
    {
        if(null !== $pTable)
        {
            $this->table($pTable);
        }
    }
    
    /**
     * @param boolean $pValue
     * @return Insert
     */
    public function raw($pValue)
    {
        $this->_raw = $pValue;
        return $this;
    }

    /**
     * @param string $pTable
     * @return Insert
     */
    public function table($pTable)
    {
        $this->_table = $pTable;
        return $this;
    }
    
    /**
     * @param array $pColumns
     * @return Insert
     */
    public function columns(array $pColumns)
    {
        $this->_columns = $pColumns;
        return $this;
    }
    
    /**
     * @param Select|string|array $pValues
     * @param string $pType
     * @return Insert
     */
    public function values($pValues, $pType = self::VALUES_SET)
    {
        if($pValues instanceof Select)
        {
            $this->_values = $pValues->getQuery();
        }
        else if(is_string($pValues))
        {
            $this->_values = $pValues;
        }
        else
        {
            $keys = array_keys($pValues);
            
            if(is_string(current($keys)))
            {
                $this->columns($keys);
            }
            
            if($pType == self::VALUES_SET || !is_array($this->_values))
            {
                $this->_values = array();
            }
            
            $this->_values[] = array_values($pValues);
        }
        
        return $this;
    }

    /**
     * @param string $pPart
     * @return Insert
     */
    public function reset($pPart)
    {
        switch($pPart)
        {
            case 'columns':
                $this->_columns = array();
            break;
            case 'values':
                $this->_values = null;
            break;
            case 'data':
                $this->_columns = array();
                $this->_values = null;
            break;
        }
        
        return $this;
    }
    
    /**
     * @see SQLAbstract::render()
     */
    public function render()
    {
        $sql = 'INSERT ' . "\n";
        $sql .= 'INTO ' . $this->_table . ' ' . "\n";
        $sql .= $this->renderColumns();
        $sql .= $this->renderValues();

        return trim($sql);
    }
    
    /**
     * @return string
     */
    protected function renderColumns()
    {
        $sql = '';
        
        if(empty($this->_values))
        {
            $sql = '';
        }
        else if(count($this->_columns) > 0)
        {
            $sql .= '(' . implode(', ' . "\n", $this->_columns) . ') ' . "\n";
        }
        
        return $sql;
    }
    
    /**
     * @return string
     */
    protected function renderValues()
    {
        $sql = '';
        
        if(empty($this->_values))
        {
            $sql .= 'DEFAULT VALUES';
        }
        if(is_string($this->_values))
        {
            $sql .= '(' . $this->_values . ') ' . "\n";
        }
        else
        {
            $sql .= 'VALUES ';
            $first = true;
            $me = $this;

            foreach($this->_values as $values)
            {
                if(!$this->_raw)
                {
                    $values = array_map(function($value) use($me)
                    {
                        return $me->quote($value);
                    }, 
                    $values);
                }

                $sql .= ($first ? '' : ', ') . '(' . implode(', ' . "\n", $values) . ') ' . "\n";
                $first = false;
            }
        }
        
        return $sql;
    }
}
