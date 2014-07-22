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
     * @param array|string $pOrder
     * @param string $pType
     * @return Delete
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
     * @return Delete
     */
    public function limit($pLimit)
    {
        $this->_limit = (int)$pLimit;
        return $this;
    }
    
    /**
     * @param integer $pOffset
     * @return Delete
     */
    public function offset($pOffset)
    {
        $this->_offset = (int)$pOffset;
        return $this;
    }
    
    /**
     * @see BaseDelete::render()
     */
    public function render()
    {
        $SQL = 'DELETE FROM ' . "\n";
        $SQL .= $this->_table . ' ' . "\n";
        $SQL .= $this->renderWheres();
        $SQL .= $this->renderOrders();
        $SQL .= $this->renderLimit();

        return trim($SQL);
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
                $SQL .= ($first ? '' : ', ') . $order['column'] . (self::ORDER_NONE === $order['type'] ? '' : ' COLLATE NOCASE ' . $order['type']);
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
            $SQL .= 'LIMIT ' . $this->_limit . ' ';
        }
        
        if(null !== $this->_offset)
        {
            $SQL .= 'OFFSET ' . $this->_offset . ' ';
        }
        
        if(!empty($SQL))
        {
            $SQL .= "\n";
        }
        
        return $SQL;
    }
}
