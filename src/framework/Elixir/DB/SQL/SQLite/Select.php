<?php

namespace Elixir\DB\SQL\SQLite;

use Elixir\DB\SQL\Select as BaseSelect;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Select extends BaseSelect
{
    /**
     * @see \Elixir\DB\SQL\Select::renderOrders()
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
}
