<?php

namespace Elixir\DB\SQL;

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
    protected $_sets = array();
    
    /**
     * @var array 
     */
    protected $_wheres = array();
    
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
            call_user_func_array($pCond, array($where));
            $pCond = $where->render();
        }
        
        $this->_wheres[] = array('query' => $this->assemble($pCond, $pValue), 'type' => 'AND');
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
            call_user_func_array($pCond, array($where));
            $pCond = $where->render();
        }
        
        $this->_wheres[] = array('query' => $this->assemble($pCond, $pValue), 'type' => 'OR');
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
                $this->_wheres = array();
            break;
            case 'set':
                $this->_sets = array();
            break;
        }
        
        return $this;
    }
    
    /**
     * @see SQLAbstract::render()
     */
    public function render()
    {
        $sql = 'UPDATE ' . "\n";
        $sql = $this->_table . ' ' . "\n";
        $sql .= $this->renderSets();
        $sql .= $this->renderWheres();

        return trim($sql);
    }
    
    /**
     * @return string
     */
    protected function renderSets()
    {
        $sql = 'SET ';
        $sets = array();
        
        foreach($this->_sets as $key => $value)
        {
            if(!$this->_raw)
            {
                $value = $this->quote($value);
            }
            
            $sets[] = $key . ' = ' . $value;
        }

        $sql .= implode(', ', $sets) . ' ' . "\n";
        return $sql;
    }
    
    /**
     * @return string
     */
    protected function renderWheres()
    {
        $sql = '';
        
        if(count($this->_wheres) > 0)
        {
            $sql .= 'WHERE ';
            $first = true;
            
            foreach($this->_wheres as $where)
            {
                $sql .= ($first ? '' : $where['type'] . ' ') . (substr(trim($where['query']), 0, 1) != '(' ? '(' . $where['query'] . ')' : $where['query']) . "\n";
                $first = false;
            }
            
            $sql .= ' ';
        }
        
        return $sql;
    }
}
