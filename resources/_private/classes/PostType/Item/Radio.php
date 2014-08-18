<?php

namespace Isatech\PostType\Item;

class Radio extends ItemAbstract
{
    protected $_template = 'radio';
    protected $_data = [];
    
    public function setData(array $pValue)
    {
        $this->_data = $pValue;
    }
    
    public function getData()
    {
        return $this->_data;
    }
    
    public function setAttributes(array $pValue)
    {
        $this->_attributes = $pValue;
        $this->_attributes['name'] = $this->_name;
        $this->_attributes['type'] = 'radio';
    }
}