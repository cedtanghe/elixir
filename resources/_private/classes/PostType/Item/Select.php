<?php

namespace Isatech\PostType\Item;

class Select extends ItemAbstract
{
    protected $_template = 'select';
    protected $_data = [];
    
    public function setData(array $pValue)
    {
        $this->_data = $pValue;
    }
    
    public function getData()
    {
        return $this->_data;
    }
    
    public function isEmpty() 
    {
        return isset($this->_attributes['multiple']) ? count($this->getValue()) == 0 : empty($this->getValue());
    }
    
    public function getValue() 
    {
        return get_post_meta($this->_postId, $this->_name, isset($this->_attributes['multiple']) ? false : true);
    }
}