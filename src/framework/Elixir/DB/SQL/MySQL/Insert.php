<?php

namespace Elixir\DB\SQL\MySQL;

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
     * @var array
     */
    protected $_duplicateKeyUpdate = [];
    
    /**
     * @param boolean $pValue
     * @return Insert
     */
    public function ignore($pValue = true)
    {
        $this->_ignore = $pValue;
        return $this;
    }
    
    /**
     * @param array $pValues
     * @return Insert
     */
    public function duplicateKeyUpdate(array $pValues)
    {
        $this->_duplicateKeyUpdate = $pValues;
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
        $sql .= $this->renderDuplicateKeyUpdate();

        return trim($sql);
    }
    
    /**
     * @return string
     */
    protected function renderDuplicateKeyUpdate()
    {
        if(count($this->_duplicateKeyUpdate) > 0)
        {
            $sql = 'ON DUPLICATE KEY UPDATE ';
            $first = true;
            $me = $this;

            foreach($this->_duplicateKeyUpdate as $key => $value)
            {
                if(!$this->_raw)
                {
                    $value =  $me->quote($value);
                }

                $sql .= ($first ? '' : ', ') . $key . ' = ' . $value . "\n";
                $first = false;
            }
            
            
            return $sql . "\n";
        }
        
        return '';
    }
    
    /**
     * @return string
     */
    protected function renderIgnore()
    {
        if($this->_ignore)
        {
            return 'IGNORE ' . "\n";
        }
        
        return '';
    }
    
    /**
     * @see BaseInsert::renderColumns()
     */
    protected function renderColumns() 
    {
        $sql = parent::renderColumns();
        
        if(empty($this->_values))
        {
            $sql = '() ' . "\n";
        }
        
        return $sql;
    }
    
    /**
     * @see BaseInsert::renderValues()
     */
    protected function renderValues() 
    {
        $sql = parent::renderValues();
        
        if(empty($this->_values))
        {
            $sql = 'VALUES () ' . "\n";
        }
        
        return $sql;
    }
}
