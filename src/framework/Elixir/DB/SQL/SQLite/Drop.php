<?php

namespace Elixir\DB\SQL\SQLite;

use Elixir\DB\SQL\Drop as BaseDrop;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Drop extends BaseDrop
{
    /**
     * @var boolean 
     */
    protected $_ifExists = false;
    
    /**
     * @param boolean $pValue
     * @return Drop
     */
    public function ifExists($pValue)
    {
        $this->_ifExists = (bool)$pValue;
        return $this;
    }
    
    /**
     * @see BaseDrop::render()
     */
    public function render()
    {
        $sql = 'DROP TABLE ' . "\n";
        $sql .= $this->renderIfExists();
        $sql .= $this->_table;

        return trim($sql);
    }
    
    /**
     * @return string
     */
    protected function renderIfExists()
    {
        $sql = '';
        
        if($this->_ifExists)
        {
            $sql .= 'IF EXISTS ' . "\n";
        }
        
        return $sql;
    }
}
