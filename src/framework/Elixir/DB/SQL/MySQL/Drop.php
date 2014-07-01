<?php

namespace Elixir\DB\SQL\MySQL;

use Elixir\DB\SQL\Drop as BaseDrop;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Drop extends BaseDrop
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
     * @return Drop
     */
    public function temporary($pValue)
    {
        $this->_temporary = (bool)$pValue;
        return $this;
    }
    
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
        $sql = 'DROP ' . "\n";
        $sql .= $this->renderTemporary();
        $sql .= 'TABLE ' . "\n";
        $sql .= $this->renderIfExists();
        $sql .= $this->_table;

        return trim($sql);
    }
    
    /**
     * @return string
     */
    protected function renderTemporary()
    {
        $sql = '';
        
        if($this->_temporary)
        {
            $sql .= 'TEMPORARY ' . "\n";
        }
        
        return $sql;
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
