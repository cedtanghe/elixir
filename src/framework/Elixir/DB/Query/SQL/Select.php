<?php

namespace Elixir\DB\SQL;

use Elixir\DB\SQL\JoinClause;
use Elixir\DB\SQL\SQLAbstract;
use Elixir\DB\SQL\WhereClause;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Select extends SQLAbstract
{
    /**
     * @var string 
     */
    protected $_quantifier;
    
    /**
     * @var array 
     */
    protected $_columns = [];
    
    /**
     * @var string 
     */
    protected $_table;
    
    /**
     * @var array 
     */
    protected $_joins = [];
    
    /**
     * @var array 
     */
    protected $_wheres = [];
    
    /**
     * @var array 
     */
    protected $_groups = [];
    
    /**
     * @var array 
     */
    protected $_havings = [];
    
    /**
     * @var array 
     */
    protected $_orders = [];
    
    /**
     * @var integer 
     */
    protected $_limit;
    
    /**
     * @var integer 
     */
    protected $_offset;
    
    /**
     * @var array 
     */
    protected $_combines = [];
    
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
     * @param string $pQuantifier
     * @return Select
     */
    public function quantifier($pQuantifier)
    {
        $this->_quantifier = $pQuantifier;
        return $this;
    }
    
    /**
     * @param array|string $pColumns
     * @param boolean $pReset
     * @return Select
     */
    public function columns($pColumns = self::STAR, $pReset = false)
    {
        if($pReset)
        {
            $this->_columns = [];
        }
        
        $this->_columns = array_merge($this->_columns, (array)$pColumns);
        return $this;
    }
    
    /**
     * @param string $pTable
     * @return Select
     */
    public function table($pTable)
    {
        $this->_table = $pTable;
        return $this;
    }
    
    /**
     * @param string $pTable
     * @param mixed $pCond
     * @param mixed $pValue
     * @param array|string $pColumns
     * @param string $pType
     * @return Select
     */
    public function join($pTable, $pCond, $pValue = null, $pColumns = null, $pType = self::JOIN_INNER)
    {
        if(is_callable($pCond))
        {
            $join = new JoinClause($this);
            $pCond($join);
            $pCond = $join->render();
        }
        
        $this->_joins[] = ['query' => $this->assemble($pCond, $pValue), 'type' => $pType, 'table' => $pTable];
            
        if(null !== $pColumns)
        {
            $this->columns($pColumns, false);
        }
        
        return $this;
    }
    
    /**
     * @param mixed $pCond
     * @param mixed $pValue
     * @return Select
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
     * @return Select
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
     * @param array|string $pGroup
     * @return Select
     */
    public function groupBy($pGroup)
    {
        $this->_groups = array_merge($this->_groups, (array)$pGroup);
        return $this;
    }
    
    /**
     * @param mixed $pCond
     * @param mixed $pValue
     * @return Select
     */
    public function having($pCond, $pValue = null)
    {
        if(is_callable($pCond))
        {
            $where = new WhereClause($this);
            $pCond($where);
            $pCond = $where->render();
        }
        
        $this->_havings[] = ['query' => $this->assemble($pCond, $pValue), 'type' => 'AND'];
        return $this;
    }
    
    /**
     * @param mixed $pCond
     * @param mixed $pValue
     * @return Select
     */
    public function orHaving($pCond, $pValue = null)
    {
        if(is_callable($pCond))
        {
            $where = new WhereClause($this);
            $pCond($where);
            $pCond = $where->render();
        }
        
        $this->_havings[] = ['query' => $this->assemble($pCond, $pValue), 'type' => 'OR'];
        return $this;
    }
    
    /**
     * @param array|string $pOrder
     * @param string $pType
     * @return Select
     */
    public function orderBy($pOrder, $pType = self::ORDER_ASCENDING)
    {
        foreach((array)$pOrder as $order)
        {
            $this->_orders[] = ['column' => $order, 'type' => $pType];
        }
        
        return $this;
    }
    
    /**
     * @param integer $pLimit
     * @return Select
     */
    public function limit($pLimit)
    {
        $this->_limit = $pLimit;
        return $this;
    }
    
    /**
     * @param integer $pOffset
     * @return Select
     */
    public function offset($pOffset)
    {
        $this->_offset = $pOffset;
        return $this;
    }

    /**
     * @param array $pSQLs
     * @param string $pType
     * @return Select
     */
    public function combine(array $pSQLs, $pType = self::COMBINE_UNION)
    {
        $this->_combines['SQLs'] = $pSQLs;
        $this->_combines['type'] = $pType;
        
        return $this;
    }
    
    /**
     * @param string $pPart
     * @return Select
     */
    public function reset($pPart)
    {
        switch($pPart)
        {
            case 'quantifier':
                $this->_quantifier = null;
            break;
            case 'columns':
                $this->_columns = [];
            break;
            case 'join':
                $this->_joins = [];
            break;
            case 'where':
                $this->_wheres = [];
            break;
            case 'group':
                $this->_groups = [];
            break;
            case 'having':
                $this->_havings = [];
            break;
            case 'order':
                $this->_orders = [];
            break;
            case 'limit':
                $this->_limit = null;
            break;
            case 'offset':
                $this->_offset = null;
            break;
            case 'combine':
                $this->_combines = [];
            break;
        }
        
        return $this;
    }
    
    /**
     * @see SQLAbstract::render()
     */
    public function render()
    {
        $SQL = '';
        
        if(count($this->_combines) > 0)
        {
            $SQL .= '(' . implode(')' . "\n" . $this->_combines['type'] . "\n" . '(', $this->_combines['SQLs']) . ') ' . "\n";
            $SQL .= $this->renderOrders();
            $SQL .= $this->renderLimit();
        }
        else
        {
            $SQL .= 'SELECT ' . "\n";
            $SQL .= $this->renderQuantifier();
            $SQL .= $this->renderColumns();
            $SQL .= 'FROM ' . $this->_table . ' ' . "\n";
            $SQL .= $this->renderJoins();
            $SQL .= $this->renderWheres();
            $SQL .= $this->renderGroups();
            $SQL .= $this->renderHavings();
            $SQL .= $this->renderOrders();
            $SQL .= $this->renderLimit();
        }

        return trim($SQL);
    }
    
    /**
     * @return string
     */
    protected function renderQuantifier()
    {
        if(null !== $this->_quantifier)
        {
            return $this->_quantifier . ' ' . "\n";
        }
        
        return '';
    }
    
    /**
     * @return string
     */
    protected function renderColumns()
    {
        if(count($this->_columns) > 0)
        {
            return implode(', ', $this->_columns) . ' ' . "\n";
        }
        
        if(preg_match('/as\s+(.+)$/i', $this->_table, $matches))
        {
            return $matches[1] . '.* ' . "\n";
        }
        
        return $this->_table . '.* ' . "\n";
    }
    
    /**
     * @return string
     */
    protected function renderJoins()
    {
        $SQL = '';
        
        if(count($this->_joins) > 0)
        {
            $first = true;
            
            foreach($this->_joins as $join)
            {
                $query = $join['query'];

                if(substr($query, 3) != 'ON ' && substr($query, 6) != 'USING ')
                {
                    $query = 'ON ' . (substr(trim($query), 0, 1) != '(' ? '(' . $query . ')' : $query);
                }

                $SQL .= ($first ? $join['type'] : ' ' . $join['type']) . ' JOIN ' . $join['table'] . ' ' . $query . "\n";
                $first = false;
            }
            
            $SQL .= ' ';
        }
        
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
    
    /**
     * @return string
     */
    protected function renderGroups()
    {
        $SQL = '';
        
        if(count($this->_groups) > 0)
        {
            $SQL .= 'GROUP BY ';
            $SQL .= implode(', ', $this->_groups) . ' ' . "\n";
        }
        
        return $SQL;
    }
    
    /**
     * @return string
     */
    protected function renderHavings()
    {
        $SQL = '';
        
        if(count($this->_havings) > 0)
        {
            $SQL .= 'HAVING ';
            $first = true;
            
            foreach($this->_havings as $having)
            {
                $SQL .= ($first ? '' : $having['type'] . ' ') . (substr(trim($having['query']), 0, 1) != '(' ? '(' . $having['query'] . ')' : $having['query']) . "\n";
                $first = false;
            }
            
            $SQL .= ' ';
        }
        
        return $SQL;
    }
    
    /**
     * @return string
     */
    protected function renderOrders()
    {
        $SQL = '';
        
        if(count($this->_orders) > 0)
        {
            $SQL .= 'ORDER BY ';
            $first = true;
            
            foreach($this->_orders as $order)
            {
                $SQL .= ($first ? '' : ', ') . $order['column'] . (self::ORDER_NONE === $order['type'] ? '' : ' ' . $order['type']);
                $first = false;
            }
            
            $SQL .= ' ' . "\n";
        }

        return $SQL;
    }
    
    /**
     * @return string
     */
    protected function renderLimit()
    {
        $SQL = '';
        
        if(null !== $this->_limit)
        {
            $SQL .= sprintf('LIMIT %d', $this->_limit) . ' ';
        }
        
        if(null !== $this->_offset)
        {
            $SQL .= sprintf('OFFSET %d', $this->_offset) . ' ';
        }
        
        if(!empty($SQL))
        {
            $SQL .= "\n";
        }
        
        return $SQL;
    }
}
