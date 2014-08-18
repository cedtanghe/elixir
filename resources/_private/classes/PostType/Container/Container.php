<?php

namespace Isatech\PostType\Container;

use Isatech\PostType\Item\ItemInterface;
use Isatech\ScreenError;

class Container
{
    protected $_identifier;
    protected $_validation;
    protected $_template = 'container';
    protected $_weight = 0;
    protected $_items = [];
    
    public function __construct($pIdentifier) 
    {
        $this->_identifier = $pIdentifier;
    }

    public function getIdentifier()
    {
        return $this->_identifier;
    }
    
    public function setValidation(\Closure $pValue)
    {
        $this->_validation = $pValue;
    }
    
    public function getValidation()
    {
        return $this->_validation;
    }

    public function setTemplate($pValue)
    {
        $this->_template = $pValue;
    }
    
    public function getTemplate()
    {
        return $this->_template;
    }
    
    public function setWeight($pValue)
    {
        $this->_weight = $pValue;
    }
    
    public function getWeight()
    {
        return $this->_weight;
    }
    
    public function addItem(ItemInterface $pItem)
    {
        $this->_items[] = $pItem;
    }
    
    public function getItem($pName)
    {
        foreach($this->_items as $item)
        {
            if($item->getName() == $pName)
            {
                return $item;
            }
        }
        
        return null;
    }
    
    public function setItems(array $pValue)
    {
        $this->_items = [];
        
        foreach($pValue as $item)
        {
            $this->addItem($item);
        }
    }
    
    public function getItems()
    {
        return $this->_items;
    }
    
    public function save($pPostId, ScreenError $pScreenError)
    {
        if(!$this->verifyNonce())
        {
            $pScreenError->add('CSRF', __('Jeton invalide.', THEME_TEXT_DOMAIN));
            return false;
        }
        
        $success = true;
        
        foreach($this->_items as $item)
        {
            if(false === $item->save($pPostId))
            {
                foreach($item->getErrors() as $error)
                {
                    $pScreenError->add($item->getName(), $error);
                }
                
                $success = false;
            }
        }
        
        if(null !== $this->_validation)
        {
            $v = $this->_validation;
            
            if(false === $v($this, $pScreenError))
            {
                $success = false;
            }
        }
        
        return $success;
    }
    
    public function nonce()
    {
        wp_nonce_field(
            str_replace('-', '_', $this->_identifier) . '_container', 
            str_replace('-', '_', $this->_identifier) . '_wpnonce'
        );
    }
    
    public function verifyNonce()
    {
        if(isset($_POST[str_replace('-', '_', $this->_identifier) . '_wpnonce']))
        {
            return wp_verify_nonce(
                $_POST[str_replace('-', '_', $this->_identifier) . '_wpnonce'],
                str_replace('-', '_', $this->_identifier) . '_container'
            );
        }
        
        return false;
    }
    
    public function render($pPost)
    {
        foreach($this->_items as $item)
        {
            $item->setPostId($pPost->ID);
        }

        $this->nonce();
        include get_template_directory() . '/templates/container/' . $this->_template . '.php';
    }
}