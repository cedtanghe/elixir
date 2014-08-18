<?php

namespace Isatech\PostType\Item;

class Input extends ItemAbstract
{
    protected $_template = 'input';
    
    public function setAttributes(array $pValue)
    {
        $this->_attributes = $pValue;
        $this->_attributes['name'] = $this->_name;
        $this->_attributes['type'] = 'text';
    }
}