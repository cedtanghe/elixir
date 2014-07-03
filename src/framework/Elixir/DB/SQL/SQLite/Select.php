<?php

namespace Elixir\DB\SQL\SQLite;

use Elixir\DB\SQL\Select as BaseSelect;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Select extends BaseSelect
{
    /**
     * @see BaseSelect::renderOrders()
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
}
