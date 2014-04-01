<?php

namespace Elixir\DB\SQL\SQLite;

use Elixir\DB\SQL\Delete as BaseDelete;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Delete extends BaseDelete
{
    /**
     * @var array 
     */
    protected $_orders = array();
    
    /**
     * @var integer 
     */
    protected $_limit;
    
    /**
     * @var integer 
     */
    protected $_offset;
    
    /**
     * @param array|string $pOrder
     * @param string $pType
     * @return Delete
     */
    public function orderBy($pOrder, $pType = self::ORDER_ASCENDING)
    {
        foreach((array)$pOrder as $order)
        {
            $this->_orders[] = array('column' => $order, 'type' => $pType);
        }
        
        return $this;
    }
    
    /**
     * @param integer $pLimit
     * @return Delete
     */
    public function limit($pLimit)
    {
        $this->_limit = $pLimit;
        return $this;
    }
    
    /**
     * @param integer $pOffset
     * @return Delete
     */
    public function offset($pOffset)
    {
        $this->_offset = $pOffset;
        return $this;
    }
    
    /**
     * @see \Elixir\DB\SQL\Delete::render()
     */
    public function render()
    {
        $sql = 'DELETE FROM ' . "\n";
        $sql .= $this->_table . ' ' . "\n";
        $sql .= $this->renderWheres();
        $sql .= $this->renderOrders();
        $sql .= $this->renderLimit();

        return trim($sql);
    }
    
    /**
     * @return string
     */
    protected function renderOrders()
    {
        $sql = '';
        
        if(count($this->_orders) > 0)
        {
            $sql .= 'ORDER BY ';
            $first = true;
            
            foreach($this->_orders as $order)
            {
                $sql .= ($first ? '' : ', ') . $order['column'] . (self::ORDER_NONE === $order['type'] ? '' : ' COLLATE NOCASE ' . $order['type']);
                $first = false;
            }
            
            $sql .= ' ' . "\n";
        }

        return $sql;
    }
    
    /**
     * @return string
     */
    protected function renderLimit()
    {
        $sql = '';
        
        if(null !== $this->_limit)
        {
            $sql .= 'LIMIT ' . $this->_limit . ' ';
        }
        
        if(null !== $this->_offset)
        {
            $sql .= 'OFFSET ' . $this->_offset . ' ';
        }
        
        if(!empty($sql))
        {
            $sql .= "\n";
        }
        
        return $sql;
    }
}
