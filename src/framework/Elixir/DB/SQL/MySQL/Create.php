<?php

namespace Elixir\DB\SQL\MySQL;

use Elixir\DB\SQL\Create as BaseCreate;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Create extends BaseCreate
{
    /**
     * @var boolean 
     */
    protected $_ifNotExists = false;
    
    /**
     * @param boolean $pValue
     * @return Create
     */
    public function ifNotExists($pValue)
    {
        $this->_ifNotExists = (bool)$pValue;
        return $this;
    }
    
    /**
     * @see BaseCreate::render()
     */
    public function render()
    {
        $sql = 'CREATE ' . "\n";
        $sql .= $this->renderTemporary();
        $sql .= 'TABLE ' . "\n";
        $sql .= $this->renderIfNotExists();
        $sql .= $this->_table . ' ' . "\n";
        $sql .= $this->renderColumns();
        $sql .= $this->renderOptions();

        return trim($sql);
    }
    
    /**
     * @return string
     */
    protected function renderIfNotExists()
    {
        $sql = '';
        
        if($this->_ifNotExists)
        {
            $sql .= 'IF NOT EXISTS ' . "\n";
        }
        
        return $sql;
    }
}
