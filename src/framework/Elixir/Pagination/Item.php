<?php

namespace Elixir\Pagination;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Item
{
    /**
     * @param integer $pIndex
     */
    public function __construct($pIndex) 
    {
        $this->_index = $pIndex;
    }

    /**
     * @return integer
     */
    public function getIndex()
    {
        return $this->_index;
    }
    
    /**
     * @param boolean $pValue
     */
    public function setSelected($pValue)
    {
        $this->_selected = $pValue;
    }
    
    /**
     * @return boolean
     */
    public function isSelected()
    {
        return $this->_selected;
    }
    
    /**
     * @param boolean $pValue
     */
    public function setFirst($pValue)
    {
        $this->_first = $pValue;
    }
    
    /**
     * @return boolean
     */
    public function isFirst()
    {
        return $this->_first;
    }
    
    /**
     * @param boolean $pValue
     */
    public function setLast($pValue)
    {
        $this->_last = $pValue;
    }
    
    /**
     * @return boolean
     */
    public function isLast()
    {
        return $this->_last;
    }
    
    /**
     * @return string
     */
    public function __toString() 
    {
        return (string)$this->getIndex();
    }
}
