<?php

namespace Elixir\DB\Query\SQL;

use Elixir\DB\Query\SQL\SQLInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class WhereClause 
{
    /**
     * @var SQLInterface
     */
    protected $SQL;

    /**
     * @var array 
     */
    protected $wheres = [];

    /**
     * @param SQLInterface $SQL
     */
    public function __construct(SQLInterface $SQL) 
    {
        $this->SQL = $SQL;
    }

    /**
     * @param string|callable $condition
     * @param mixed $value
     * @return WhereClause
     */
    public function where($condition, $value = null)
    {
        if (is_callable($condition)) 
        {
            $where = new static($this->SQL);
            call_user_func_array($condition, [$where]);
                    
            $condition = $where->render();
        }

        $this->wheres[] = ['query' => $this->SQL->assemble($condition, $value), 'type' => 'AND'];
        return $this;
    }

    /**
     * @param string|callable $condition
     * @param mixed $value
     * @return WhereClause
     */
    public function orWhere($condition, $value = null) 
    {
        if (is_callable($condition))
        {
            $where = new static($this->SQL);
            call_user_func_array($condition, [$where]);
                    
            $condition = $where->render();
        }

        $this->wheres[] = ['query' => $this->SQL->assemble($condition, $value), 'type' => 'OR'];
        return $this;
    }

    /**
     * @return WhereClause
     */
    public function reset() 
    {
        $this->wheres = [];
        return $this;
    }
    
    /**
     * @return array
     */
    public function get() 
    {
        return $this->wheres;
    }
    
    /**
     * @param array $data
     * @return WhereClause
     */
    public function merge(array $data) 
    {
        $this->wheres = array_merge($this->wheres, $data);
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

        foreach ($this->wheres as $where) 
        {
            $SQL .= ($first ? '' : $where['type'] . ' ') . '(' . $where['query'] . ')' . "\n";
            $first = false;
        }

        if (count($this->wheres) > 1) 
        {
            $SQL = '(' . $SQL . ')';
        }

        return $SQL;
    }

    /**
     * @ignore
     */
    public function __toString() 
    {
        return $this->render();
    }
}
