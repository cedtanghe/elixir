<?php

namespace Isatech\PostType\Item;

class Radio extends ItemAbstract
{
    protected $_template = 'checkbox';
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
        $this->_attributes['type'] = 'checkbox';
    }
    
    public function isEmpty() 
    {
        return count($this->getValue()) == 0;
    }
    
    public function getValue() 
    {
        return get_post_meta($this->_postId, $this->_name, false);
    }
}