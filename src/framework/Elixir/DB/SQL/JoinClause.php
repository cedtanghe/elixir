<?php

namespace Elixir\DB\SQL;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class JoinClause
{
    /**
     * @var SQLAbstract
     */
    protected $_SQL;
    
    /**
     * @var array 
     */
    protected $_ons = array();
    
    /**
     * @var array 
     */
    protected $_usings = array();
    
    /**
     * @param Select $pSQL
     */
    public function __construct(Select $pSQL) 
    {
        $this->_SQL = $pSQL;
    }
    
    /**
     * @param mixed $pCond
     * @param mixed $pValue
     * @return JoinClause
     */
    public function on($pCond, $pValue = null)
    {
        $this->_ons[] = array('query' => $this->_SQL->assemble($pCond, $pValue), 'type' => 'AND');
        return $this;
    }
    
    /**
     * @param mixed $pCond
     * @param mixed $pValue
     * @return JoinClause
     */
    public function orOn($pCond, $pValue = null)
    {
        $this->_ons[] = array('query' => $this->_SQL->assemble($pCond, $pValue), 'type' => 'OR');
        return $this;
    }
    
    /**
     * @param array|string $pUsing
     * @return JoinClause
     */
    public function using($pUsing)
    {
        $this->_usings = array_merge($this->_usings, (array)$pUsing);
        return $this;
    }
    
    /**
     * @param array|string $pColumns
     * @param boolean $pReset
     * @return JoinClause
     */
    public function columns($pColumns = self::STAR, $pReset = false)
    {
        $this->_SQL->columns($pColumns, $pReset);
        return $this;
    }
    
    /**
     * @param string $pPart
     * @return JoinClause
     */
    public function reset($pPart)
    {
        switch($pPart)
        {
            case 'on':
                $this->_ons = array();
            break;
            case 'using':
                $this->_usings = array();
            break;
        }
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function render()
    {
        $sql = '';
        
        if(count($this->_usings) > 0)
        {
            $sql .= $this->renderUsings();
        }
        else
        {
            $sql .= $this->renderOns();
        }
        
        return $sql;
    }
    
    /**
     * @return string
     */
    protected function renderUsings()
    {
        return 'USING (' . implode(', ', $this->_usings) . ')';
    }

    /**
     * @return string
     */
    protected function renderOns()
    {
        $sql = '';
        $first = true;

        foreach($this->_ons as $on)
        {
            $sql .= ($first ? '' : $on['type'] . ' ') . '(' . $on['query'] . ')' . "\n";
            $first = false;
        }
        
        if(count($this->_ons) > 1)
        {
            $sql = '(' . $sql . ')';
        }
        
        return $sql;
    }

    /**
     * @see JoinClause::render()
     */
    public function __toString()
    {
        return $this->render();
    }
}
