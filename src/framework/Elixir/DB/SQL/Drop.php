<?php

namespace Elixir\DB\SQL;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Drop extends SQLAbstract
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
     * @return Drop
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