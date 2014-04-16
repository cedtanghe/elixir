<?php

namespace Elixir\DB\SQL;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Delete extends SQLAbstract
{
    /**
     * @var string 
     */
    protected $_table;
    
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
     * @param string $pTable
     * @return Delete
     */
    public function table($pTable)
    {
        $this->_table = $pTable;
        return $this;
    }

    /**
     * @param mixed $pCond
     * @param mixed $pValue
     * @return Delete
     */
    public function where($pCond, $pValue = null)
    {
        if(is_callable($pCond))
        {
            $where = new WhereClause($this);
            $pCond($where);
            $pCond = $where->render();
        }
        
        $this->_wheres[] = array('query' => $this->assemble($pCond, $pValue), 'type' => 'AND');
        return $this;
    }
    
    /**
     * @param mixed $pCond
     * @param mixed $pValue
     * @return Delete
     */
    public function orWhere($pCond, $pValue = null)
    {
        if(is_callable($pCond))
        {
            $where = new WhereClause($this);
            $pCond($where);
            $pCond = $where->render();
        }
        
        $this->_wheres[] = array('query' => $this->assemble($pCond, $pValue), 'type' => 'OR');
        return $this;
    }
    
    /**
     * @param string $pPart
     * @return Delete
     */
    public function reset($pPart)
    {
        switch($pPart)
        {
            case 'where':
                $this->_wheres = array();
            break;
        }
        
        return $this;
    }
    
    /**
     * @see SQLAbstract::render()
     */
    public function render()
    {
        $sql = 'DELETE FROM ' . "\n";
        $sql = $this->_table . ' ' . "\n";
        $sql .= $this->renderWheres();

        return trim($sql);
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
