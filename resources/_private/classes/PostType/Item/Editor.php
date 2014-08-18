<?php

namespace Isatech\PostType\Item;

class Editor extends ItemAbstract
{
    protected $_template = 'editor';
    protected $_settings = [];
    
    public function setSettings(array $pValue)
    {
        $this->_settings = $pValue;
    }
    
    public function getSettings()
    {
        return $this->_settings;
    }
}