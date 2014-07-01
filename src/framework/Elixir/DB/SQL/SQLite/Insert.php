<?php

namespace Elixir\DB\SQL\SQLite;

use Elixir\DB\SQL\Insert as BaseInsert;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Insert extends BaseInsert
{
    /**
     * @var boolean
     */
    protected $_ignore = false;
    
    /**
     * @param boolean $pValue
     * @return Insert
     */
    public function ignore($pValue)
    {
        $this->_ignore = $pValue;
        return $this;
    }
    
    /**
     * @see BaseInsert::render()
     */
    public function render()
    {
        $sql = 'INSERT ' . "\n";
        $sql .= $this->renderIgnore();
        $sql .= 'INTO ' . $this->_table . ' ' . "\n";
        $sql .= $this->renderColumns();
        $sql .= $this->renderValues();

        return trim($sql);
    }
    
    /**
     * @return string
     */
    protected function renderIgnore()
    {
        $sql = '';
        
        if($this->_ignore)
        {
            $sql = 'OR IGNORE ' . "\n";
        }
        
        return $sql;
    }
}
