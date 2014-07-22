<?php

namespace Elixir\DB\SQL\MySQL;

use Elixir\DB\SQL\Update as BaseUpdate;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Update extends BaseUpdate
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
     * @return Update
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
     * @return Update
     */
    public function limit($pLimit)
    {
        $this->_limit = $pLimit;
        return $this;
    }
    
    /**
     * @param integer $pOffset
     * @return Update
     */
    public function offset($pOffset)
    {
        $this->_offset = $pOffset;
        return $this;
    }
    
    /**
     * @see BaseUpdate::render()
     */
    public function render()
    {
        $SQL = 'UPDATE ' . "\n";
        $SQL .= $this->_table . ' ' . "\n";
        $SQL .= $this->renderSets();
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
            $SQL .= $this->assemble('LIMIT ?', $this->_limit) . ' ';
        }
        
        if(null !== $this->_offset)
        {
            $SQL .= $this->assemble('OFFSET ?', $this->_offset) . ' ';
        }
        
        if(!empty($SQL))
        {
            $SQL .= "\n";
        }
        
        return $SQL;
    }
}
