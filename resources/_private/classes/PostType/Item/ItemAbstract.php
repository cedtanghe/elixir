<?php

namespace Isatech\PostType\Item;

abstract class ItemAbstract implements ItemInterface
{
    protected $_name;
    protected $_label;
    protected $_attributes = [];
    protected $_required = false;
    protected $_validation;
    protected $_filter;
    protected $_errors = [];
    protected $_template;
    protected $_postId;
    
    public function __construct($pName) 
    {
        $this->_name = $pName;
        $this->setAttributes([]);
    }
    
    public function getName()
    {
        return $this->_name;
    }
    
    public function setLabel($pValue)
    {
        $this->_label = $pValue;
    }
    
    public function getLabel()
    {
        return $this->_label;
    }
    
    public function setAttributes(array $pValue)
    {
        $this->_attributes = $pValue;
        $this->_attributes['name'] = $this->_name;
    }
    
    public function getAttributes()
    {
        return $this->_attributes;
    }
    
    public function setRequired($pValue)
    {
        $this->_required = (bool) $pValue;
    }
    
    public function isRequired()
    {
        return $this->_required;
    }
    
    public function setValidation(\Closure $pValue)
    {
        $this->_validation = $pValue;
    }
    
    public function getValidation()
    {
        return $this->_validation;
    }
    
    public function setFilter(\Closure $pValue)
    {
        $this->_filter = $pValue;
    }
    
    public function getFilter()
    {
        return $this->_filter;
    }

    public function setTemplate($pValue)
    {
        $this->_template = $pValue;
    }
    
    public function getTemplate()
    {
        return $this->_template;
    }
    
    public function setPostId($pValue)
    {
        $this->_postId = $pValue;
    }
    
    public function getPostId()
    {
        return $this->_postId;
    }

    public function save($pPostId)
    {
        $this->_postId = $pPostId;
        
        if(isset($_POST[$this->_name]) && !empty($_POST[$this->_name]))
        {
            $success = true;
            $value = $_POST[$this->_name];
            
            if(null !== $this->_validation)
            {
                $v = $this->_validation;
                
                if(false === $v($value, $this->_errors))
                {
                    $success = false;
                }
            }
            
            if($success && null !== $this->_filter)
            {
                $f = $this->_filter;
                $value = $f($value);
            }
            
            delete_post_meta($this->_postId, $this->_name);
            
            foreach((array)$value as $v)
            {
                add_post_meta($this->_postId, $this->_name, $v);
            }
            
            return $success;
        }
        else if($this->_required)
        {
            $this->_errors[] = __('Required field', THEME_TEXT_DOMAIN);
            return false;
        }
        else
        {
            delete_post_meta($this->_postId, $this->_name);
        }
        
        return true;
    }
    
    public function isEmpty() 
    {
        return empty($this->getValue());
    }
    
    public function getValue() 
    {
        return get_post_meta($this->_postId, $this->_name, true);
    }
    
    public function render()
    {
        $this->_errors = [];
        include get_template_directory() . '/templates/item/' . $this->_template . '.php';
    }
    
    public function getErrors()
    {
        return $this->_errors;
    }
}