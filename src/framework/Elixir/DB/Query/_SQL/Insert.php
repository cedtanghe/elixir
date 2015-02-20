<?php

namespace Elixir\DB\Query\SQL;

use Elixir\DB\Query\SQL\Select;
use Elixir\DB\Query\SQL\SQLAbstract;

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
     * @param Select|array $pValues
     * @param string $pType
     * @return Insert
     */
    public function values($pValues, $pType = self::VALUES_SET)
    {
        if($pValues instanceof Select)
        {
            $this->_values = $pValues->getQuery();
        }
        else
        {
            if($pType == self::VALUES_SET || !is_array($this->_values))
            {
                $this->_values = [];
            }
            
            $columns = false;
            
            foreach((array)$pValues as $key => $value)
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
        $SQL = 'INSERT ' . "\n";
        $SQL .= 'INTO ' . $this->_table . ' ' . "\n";
        $SQL .= $this->renderColumns();
        $SQL .= $this->renderValues();

        return trim($SQL);
    }
    
    /**
     * @return string
     */
    protected function renderColumns()
    {
        $SQL = '';
        
        if(empty($this->_values))
        {
            $SQL = '';
        }
        else if(count($this->_columns) > 0)
        {
            $SQL .= '(' . implode(', ' . "\n", $this->_columns) . ') ' . "\n";
        }
        
        return $SQL;
    }
    
    /**
     * @return string
     */
    protected function renderValues()
    {
        $SQL = '';
        
        if(empty($this->_values))
        {
            $SQL .= 'DEFAULT VALUES';
        }
        if(is_string($this->_values))
        {
            $SQL .= '(' . $this->_values . ') ' . "\n";
        }
        else
        {
            $SQL .= 'VALUES ';
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

                $SQL .= ($first ? '' : ', ') . '(' . implode(', ' . "\n", $values) . ') ' . "\n";
                $first = false;
            }
        }
        
        return $SQL;
    }
}
