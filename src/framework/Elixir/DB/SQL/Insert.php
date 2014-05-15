<?php

namespace Elixir\DB\SQL;

use Elixir\DB\SQL\Select;
use Elixir\DB\SQL\SQLAbstract;

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
    protected $_columns = [];
    
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
            if($pType == self::VALUES_SET || !is_array($this->_values))
            {
                $this->_values = [];
            }
            
            $columns = false;
            
            foreach($pValues as $key => $value)
            {
                if(!$columns)
                {
                    if(is_string($key))
                    {
                        $this->columns(array_keys($pValues));
                        $columns = true;
                    }
                }
                
                if(is_array($value))
                {
                    if(!$columns)
                    {
                        foreach($value as $k => $v)
                        {
                            if(is_string($k))
                            {
                                $this->columns(array_keys($value));
                                $columns = true;
                            }
                            
                            break;
                        }
                    }
                    
                    $this->_values[] = array_values($value);
                }
                else
                {
                    $this->_values[] = array_values($pValues);
                    break;
                }
            }
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
                $this->_columns = [];
            break;
            case 'values':
                $this->_values = null;
            break;
            case 'data':
                $this->_columns = [];
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

            foreach($this->_values as $values)
            {
                if(!$this->_raw)
                {
                    $values = array_map(function($value)
                    {
                        return $this->quote($value);
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
