<?php

namespace Elixir\DB\SQL;

use Elixir\DB\SQL\SQLAbstract;
use Elixir\DB\SQL\WhereClause;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Update extends SQLAbstract
{
    /**
     * @var string 
     */
    const VALUES_SET = 'set';
    
    /**
     * @var string 
     */
    const VALUES_MERGE = 'merge';
    
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
    protected $_sets = [];
    
    /**
     * @var array 
     */
    protected $_wheres = [];
    
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
     * @return Update
     */
    public function raw($pValue)
    {
        $this->_raw = $pValue;
        return $this;
    }
    
    /**
     * @param string $pTable
     * @return Update
     */
    public function table($pTable)
    {
        $this->_table = $pTable;
        return $this;
    }
    
    /**
     * @param array $pValues
     * @param string $pType
     * @return Update
     */
    public function set(array $pValues, $pType = self::VALUES_SET)
    {
        if($pType == self::VALUES_SET)
        {
            $this->_sets = $pValues;
        }
        else
        {
            $this->_sets = array_merge($this->_sets, $pValues);
        }
        
        return $this;
    }

    /**
     * @param mixed $pCond
     * @param mixed $pValue
     * @return Update
     */
    public function where($pCond, $pValue = null)
    {
        if(is_callable($pCond))
        {
            $where = new WhereClause($this);
            $pCond($where);
            $pCond = $where->render();
        }
        
        $this->_wheres[] = ['query' => $this->assemble($pCond, $pValue), 'type' => 'AND'];
        return $this;
    }
    
    /**
     * @param mixed $pCond
     * @param mixed $pValue
     * @return Update
     */
    public function orWhere($pCond, $pValue = null)
    {
        if(is_callable($pCond))
        {
            $where = new WhereClause($this);
            $pCond($where);
            $pCond = $where->render();
        }
        
        $this->_wheres[] = ['query' => $this->assemble($pCond, $pValue), 'type' => 'OR'];
        return $this;
    }
    
    /**
     * @param string $pPart
     * @return Update
     */
    public function reset($pPart)
    {
        switch($pPart)
        {
            case 'where':
                $this->_wheres = [];
            break;
            case 'set':
                $this->_sets = [];
            break;
        }
        
        return $this;
    }
    
    /**
     * @see SQLAbstract::render()
     */
    public function render()
    {
        $SQL = 'UPDATE ' . "\n";
        $SQL .= $this->_table . ' ' . "\n";
        $SQL .= $this->renderSets();
        $SQL .= $this->renderWheres();

        return trim($SQL);
    }
    
    /**
     * @return string
     */
    protected function renderSets()
    {
        $SQL = 'SET ';
        $sets = [];
        
        foreach($this->_sets as $key => $value)
        {
            if(!$this->_raw)
            {
                $value = $this->quote($value);
            }
            
            $sets[] = $key . ' = ' . $value;
        }

        $SQL .= implode(', ', $sets) . ' ' . "\n";
        return $SQL;
    }
    
    /**
     * @return string
     */
    protected function renderWheres()
    {
        $SQL = '';
        
        if(count($this->_wheres) > 0)
        {
            $SQL .= 'WHERE ';
            $first = true;
            
            foreach($this->_wheres as $where)
            {
                $SQL .= ($first ? '' : $where['type'] . ' ') . (substr(trim($where['query']), 0, 1) != '(' ? '(' . $where['query'] . ')' : $where['query']) . "\n";
                $first = false;
            }
            
            $SQL .= ' ';
        }
        
        return $SQL;
    }
}
