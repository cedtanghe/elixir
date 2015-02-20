<?php

namespace Elixir\DB\Query\SQL\SQLite;

use Elixir\DB\Query\SQL\Insert as BaseInsert;

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
        $SQL = 'INSERT ' . "\n";
        $SQL .= $this->renderIgnore();
        $SQL .= 'INTO ' . $this->_table . ' ' . "\n";
        $SQL .= $this->renderColumns();
        $SQL .= $this->renderValues();

        return trim($SQL);
    }
    
    /**
     * @return string
     */
    protected function renderIgnore()
    {
        $SQL = '';
        
        if($this->_ignore)
        {
            $SQL = 'OR IGNORE ' . "\n";
        }
        
        return $SQL;
    }
}
