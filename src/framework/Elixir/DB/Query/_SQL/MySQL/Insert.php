<?php

namespace Elixir\DB\Query\SQL\MySQL;

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
        $SQL = 'INSERT ' . "\n";
        $SQL .= $this->renderIgnore();
        $SQL .= 'INTO ' . $this->_table . ' ' . "\n";
        $SQL .= $this->renderColumns();
        $SQL .= $this->renderValues();
        $SQL .= $this->renderDuplicateKeyUpdate();

        return trim($SQL);
    }
    
    /**
     * @return string
     */
    protected function renderDuplicateKeyUpdate()
    {
        if(count($this->_duplicateKeyUpdate) > 0)
        {
            $SQL = 'ON DUPLICATE KEY UPDATE ';
            $first = true;
            $me = $this;

            foreach($this->_duplicateKeyUpdate as $key => $value)
            {
                if(!$this->raw)
                {
                    $value =  $me->quote($value);
                }

                $SQL .= ($first ? '' : ', ') . $key . ' = ' . $value . "\n";
                $first = false;
            }
            
            
            return $SQL . "\n";
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
        $SQL = parent::renderColumns();
        
        if(empty($this->values))
        {
            $SQL = '() ' . "\n";
        }
        
        return $SQL;
    }
    
    /**
     * @see BaseInsert::renderValues()
     */
    protected function renderValues() 
    {
        $SQL = parent::renderValues();
        
        if(empty($this->values))
        {
            $SQL = 'VALUES () ' . "\n";
        }
        
        return $SQL;
    }
}
