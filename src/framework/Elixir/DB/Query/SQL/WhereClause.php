<?php

namespace Elixir\DB\SQL;

use Elixir\DB\SQL\SQLInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class WhereClause
{
    /**
     * @var SQLInterface
     */
    protected $_SQL;
    
    /**
     * @var array 
     */
    protected $_wheres = [];
    
    /**
     * @param SQLInterface $pSQL
     */
    public function __construct(SQLInterface $pSQL) 
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
        
        $this->_wheres[] = ['query' => $this->_SQL->assemble($pCond, $pValue), 'type' => 'AND'];
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
        
        $this->_wheres[] = ['query' => $this->_SQL->assemble($pCond, $pValue), 'type' => 'OR'];
        return $this;
    }
    
    /**
     * @return WhereClause
     */
    public function reset()
    {
        $this->_wheres = [];
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
        $SQL = '';
        $first = true;
            
        foreach($this->_wheres as $where)
        {
            $SQL .= ($first ? '' : $where['type'] . ' ') . '(' . $where['query'] . ')' . "\n";
            $first = false;
        }
        
        if(count($this->_wheres) > 1)
        {
            $SQL = '(' . $SQL . ')';
        }
        
        return $SQL;
    }

    /**
     * @see WhereClause::render()
     */
    public function __toString()
    {
        return $this->render();
    }
}
