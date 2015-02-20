<?php

namespace Elixir\DB\Query\SQL\MySQL;

use Elixir\DB\Query\SQL\DropTable as BaseDropTable;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class DropTable extends BaseDropTable
{
    /**
     * @var boolean 
     */
    protected $_temporary = false;
    
    /**
     * @var boolean 
     */
    protected $_ifExists = false;
    
    /**
     * @param boolean $pValue
     * @return DropTable
     */
    public function temporary($pValue)
    {
        $this->_temporary = (bool)$pValue;
        return $this;
    }
    
    /**
     * @param boolean $pValue
     * @return DropTable
     */
    public function ifExists($pValue)
    {
        $this->_ifExists = (bool)$pValue;
        return $this;
    }
    
    /**
     * @see BaseDropTable::render()
     */
    public function render()
    {
        $SQL = 'DROP ' . "\n";
        $SQL .= $this->renderTemporary();
        $SQL .= 'TABLE ' . "\n";
        $SQL .= $this->renderIfExists();
        $SQL .= $this->_table;

        return trim($SQL);
    }
    
    /**
     * @return string
     */
    protected function renderTemporary()
    {
        $SQL = '';
        
        if($this->_temporary)
        {
            $SQL .= 'TEMPORARY ' . "\n";
        }
        
        return $SQL;
    }
    
    /**
     * @return string
     */
    protected function renderIfExists()
    {
        $SQL = '';
        
        if($this->_ifExists)
        {
            $SQL .= 'IF EXISTS ' . "\n";
        }
        
        return $SQL;
    }
}
