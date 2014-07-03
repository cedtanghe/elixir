<?php

namespace Elixir\DB\SQL;

use Elixir\DB\SQL\Select;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
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
    protected $_ons = [];
    
    /**
     * @var array 
     */
    protected $_usings = [];
    
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
        if(is_callable($pCond))
        {
            $join = new static($this->_SQL);
            $pCond($join);
            $pCond = $join->render();
        }
        
        $this->_ons[] = ['query' => $this->_SQL->assemble($pCond, $pValue), 'type' => 'AND'];
        return $this;
    }
    
    /**
     * @param mixed $pCond
     * @param mixed $pValue
     * @return JoinClause
     */
    public function orOn($pCond, $pValue = null)
    {
        if(is_callable($pCond))
        {
            $join = new static($this->_SQL);
            $pCond($join);
            $pCond = $join->render();
        }
        
        $this->_ons[] = ['query' => $this->_SQL->assemble($pCond, $pValue), 'type' => 'OR'];
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
                $this->_ons = [];
            break;
            case 'using':
                $this->_usings = [];
            break;
        }
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function render()
    {
        $SQL = '';
        
        if(count($this->_usings) > 0)
        {
            $SQL .= $this->renderUsings();
        }
        else
        {
            $SQL .= $this->renderOns();
        }
        
        return $SQL;
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
        $SQL = '';
        $first = true;

        foreach($this->_ons as $on)
        {
            $SQL .= ($first ? '' : $on['type'] . ' ') . '(' . $on['query'] . ')' . "\n";
            $first = false;
        }
        
        if(count($this->_ons) > 1)
        {
            $SQL = '(' . $SQL . ')';
        }
        
        return $SQL;
    }

    /**
     * @see JoinClause::render()
     */
    public function __toString()
    {
        return $this->render();
    }
}
