<?php

namespace Elixir\DB\SQL;

use Elixir\DB\SQL\SQLAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class DropTable extends SQLAbstract
{
    /**
     * @var string 
     */
    protected $_table;
    
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
     * @param string $pTable
     * @return DropTable
     */
    public function table($pTable)
    {
        $this->_table = $pTable;
        return $this;
    }
    
    /**
     * @see SQLAbstract::render()
     */
    public function render()
    {
        return 'DROP TABLE ' . $this->_table;
    }
}
