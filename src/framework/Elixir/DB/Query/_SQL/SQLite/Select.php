<?php

namespace Elixir\DB\Query\SQL\SQLite;

use Elixir\DB\Query\SQL\Select as BaseSelect;

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
        
        if(count($this->order) > 0)
        {
            $SQL .= 'ORDER BY ';
            $first = true;
            
            foreach($this->order as $order)
            {
                $SQL .= ($first ? '' : ', ') . $order['column'] . (self::ORDER_NONE === $order['type'] ? '' : ' COLLATE NOCASE ' . $order['type']);
                $first = false;
            }
            
            $SQL .= ' ' . "\n";
        }

        return $SQL;
    }
}
