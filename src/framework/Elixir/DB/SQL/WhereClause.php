<?php

namespace Elixir\DB\SQL;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class WhereClause
{
    /**
     * @var SQLAbstract
     */
    protected $_SQL;
    
    /**
     * @var array 
     */
    protected $_wheres = array();
    
    /**
     * @param SQLAbstract $pSQL
     */
    public function __construct(SQLAbstract $pSQL) 
    {
        $this->_SQL = $pSQL;
    }
    
    /**
     * @param mixed $pCond
     * @param mixed $pValue
     * @return WhereClause
     */
    public function where($pCond, $pValue = null)
    {
        if(is_callable($pCond))
        {
            $where = new static($this->_SQL);
            $pCond($where);
            $pCond = $where->render();
        }
        
        $this->_wheres[] = array('query' => $this->_SQL->assemble($pCond, $pValue), 'type' => 'AND');
        return $this;
    }
    
    /**
     * @param mixed $pCond
     * @param mixed $pValue
     * @return WhereClause
     */
    public function orWhere($pCond, $pValue = null)
    {
        if(is_callable($pCond))
        {
            $where = new static($this->_SQL);
            $pCond($where);
            $pCond = $where->render();
        }
        
        $this->_wheres[] = array('query' => $this->_SQL->assemble($pCond, $pValue), 'type' => 'OR');
        return $this;
    }
    
    /**
     * @return WhereClause
     */
    public function reset()
    {
        $this->_wheres = array();
        return $this;
    }
    
    /**
     * @return string
     */
    public function render()
    {
        return $this->renderWheres();
    }
    
    /**
     * @return string
     */
    protected function renderWheres()
    {
        $sql = '';
        $first = true;
            
        foreach($this->_wheres as $where)
        {
            $sql .= ($first ? '' : $where['type'] . ' ') . '(' . $where['query'] . ')' . "\n";
            $first = false;
        }
        
        if(count($this->_wheres) > 1)
        {
            $sql = '(' . $sql . ')';
        }
        
        return $sql;
    }

    /**
     * @see WhereClause::render()
     */
    public function __toString()
    {
        return $this->render();
    }
}
