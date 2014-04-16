<?php

namespace Elixir\Form;

use Elixir\Dispatcher\Dispatcher;
use Elixir\Dispatcher\Event;
use Elixir\Form\Extension\ExtensionInterface;
use Elixir\Form\Field\FieldInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Form extends Dispatcher implements FormInterface
{
    /**
     * @var boolean
     */
    protected $_submit = false;
    
    /**
     * @var FormInterface
     */
    protected $_parent;
    
    /**
     * @var array
     */
    protected $_keys = array();
    
    /**
     * @var array
     */
    protected $_fields = array();
    
    /**
     * @var array
     */
    protected $_forms = array();
    
    /**
     * @var array
     */
    protected $_attributes = array('method' => self::POST, 'action' => '');
    
    /**
     * @var array
     */
    protected $_options = array();
    
    /**
     * @var string
     */
    protected $_errorMessage;
    
    /**
     * @var array
     */
    protected $_errors = array();
    
    /**
     * @var boolean
     */
    protected $_prepare = false;
    
    /**
     * @param string $pName
     */
    public function __construct($pName = null) 
    {
        if(null !== $pName)
        {
            $this->setName($pName);
        }
    }
    
    /**
     * @see FormInterface::setParent()
     */
    public function setParent(FormInterface $pValue)
    {
        $this->_parent = $pValue;
    }
    
    /**
     * @see FormInterface::getParent()
     */
    public function getParent()
    {
        return $this->_parent;
    }
    
    /**
     * @see FormInterface::setName()
     */
    public function setName($pValue)
    {
        $this->setAttribute('name', $pValue);
    }
    
    /**
     * @see FormInterface::getName()
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }
    
    /**
     * @return boolean
     */
    public function isRoot()
    {
        return null === $this->_parent;
    }

    /**
     * @see FormInterface::setErrorMessage()
     */
    public function setErrorMessage($pValue)
    {
        $this->_errorMessage = $pValue;
    }
    
    /**
     * @see FormInterface::getErrorMessage()
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }
    
    /**
     * @see FormInterface::prepare()
     */
    public function prepare()
    {
        if($this->_prepare)
        {
            return;
        }
        
        $this->_prepare = true;
        $this->dispatch(new FormEvent(FormEvent::PREPARE));
    }
    
    /**
     * @return boolean
     */
    public function isPrepared()
    {
        return $this->_prepare;
    }
    
    /**
     * @param ExtensionInterface $pExtension
     */
    public function addExtension(ExtensionInterface $pExtension)
    {
        $pExtension->load($this);
    }
    
    /**
     * @param ExtensionInterface $pExtension
     */
    public function removeExtension(ExtensionInterface $pExtension)
    {
        $pExtension->unload();
    }

    /**
     * @param string $pKey
     * @return boolean
     */
    public function hasAttribute($pKey)
    {
        return array_key_exists($pKey, $this->_attributes);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function getAttribute($pKey, $pDefault = null)
    {
        if($this->hasAttribute($pKey))
        {
            return $this->_attributes[$pKey];
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @param string $pKey
     * @param string $pValue
     * @throws \LogicException
     */
    public function setAttribute($pKey, $pValue)
    {
        if($pKey == 'name')
        {
            $name = $this->getAttribute('name');
            
            if($pValue != $name)
            {
                if(null !== $this->getParent())
                {
                    throw new \LogicException('You can not redefine the form name if it already has a parent.');
                }
            }
        }
        
        $this->_attributes[$pKey] = $pValue;
    }
    
    /**
     * @param string $pKey
     */
    public function removeAttribute($pKey)
    {
        unset($this->_attributes[$pKey]);
    }
    
    /**
     * @see FormInterface::getAttributes()
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }
    
    /**
     * @see FormInterface::setAttributes()
     */
    public function setAttributes(array $pData) 
    {
        $name = $this->getName();
        $this->_attributes = array();
        
        foreach($pData as $key => $value)
        {
            $this->setAttribute($key, $value);
        }
        
        if(null !== $name && !$this->hasAttribute('name'))
        {
            $this->setName($name);
        }
    }
    
    /**
     * @param string $pKey
     * @return boolean
     */
    public function hasOption($pKey)
    {
        return array_key_exists($pKey, $this->_options);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function getOption($pKey, $pDefault = null)
    {
        if($this->hasOption($pKey))
        {
            return $this->_options[$pKey];
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @param string $pKey
     * @param string $pValue
     */
    public function setOption($pKey, $pValue)
    {
        $this->_options[$pKey] = $pValue;
    }
    
    /**
     * @param string $pKey
     */
    public function removeOption($pKey)
    {
        unset($this->_options[$pKey]);
    }
    
    /**
     * @see FormInterface::getOptions()
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
    /**
     * @see FormInterface::setOptions()
     */
    public function setOptions(array $pData) 
    {
        $this->_options = array();
        
        foreach($pData as $key => $value)
        {
            $this->setOption($key, $value);
        }
    }

    /**
     * @param string $pName
     * @param boolean $pUseSubForms
     * @return boolean
     */
    public function has($pName, $pUseSubForms = false)
    {
        if(in_array($pName, $this->_keys))
        {
            return true;
        }
        
        if($pUseSubForms)
        {
            foreach($this->_forms as $form)
            {
                if($form->has($pName, true))
                {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * @see FormInterface::add()
     * @throws \LogicException
     */
    public function add($pItem)
    {
        $name = $pItem->getName();
        
        if(null === $name)
        {
            throw new \LogicException('A form item require a name.');
        }
        
        $this->_keys[] = $name;
        
        if($pItem instanceof FieldInterface)
        {
            $this->_fields[$name] = $pItem;
        }
        else
        {
            $this->_forms[$name] = $pItem;
        }
        
        $pItem->setParent($this);
    }

    /**
     * @param string $pName
     * @param boolean $pUseSubForms
     * @param mixed $pDefault
     * @return mixed
     */
    public function get($pName, $pUseSubForms = true, $pDefault = null)
    {
        if(isset($this->_fields[$pName]))
        {
            return $this->_fields[$pName];
        }
        
        if(isset($this->_forms[$pName]))
        {
            return $this->_forms[$pName];
        }
        
        if($pUseSubForms)
        {
            foreach($this->_forms as $form)
            {
                $result = $form->get($pName, $pUseSubForms);
                
                if(null !== $result)
                {
                    return $result;
                }
            }
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @see FormInterface::remove()
     */
    public function remove($pName, $pUseSubForms = false)
    {
        $pos = array_search($pName, $this->_keys);
        
        if(false !== $pos)
        {
            array_splice($this->_keys, $pos, 1);
            
            unset($this->_fields[$pName]);
            unset($this->_forms[$pName]);
        }
        else if($pUseSubForms)
        {
            foreach($this->_forms as $form)
            {
                if($form->has($pName, true))
                {
                    $form->remove($pName, true);
                    break;
                }
            }
        }
    }
    
    /**
     * @see FormInterface::gets()
     */
    public function gets($pMask = self::ALL_FIELDS)
    {
        if(($pMask & self::ONLY_FIELDS) == self::ONLY_FIELDS)
        {
            return array_values($this->_fields);
        }

        if(($pMask & self::ONLY_FORMS) == self::ONLY_FORMS)
        {
            return array_values($this->_forms);
        }

        $items = array_merge($this->_fields, $this->_forms);
        $recursive = ($pMask & self::ALL_FIELDS) == self::ALL_FIELDS;
        $result = array();

        foreach($this->_keys as $key)
        {
            if(!$recursive)
            {
                $result[] = $items[$key];
            }
            else
            {
                if($items[$key] instanceof FormInterface)
                {
                    $result = array_merge($result, $items[$key]->gets($pMask));
                }
                else
                {
                    $result[] = $items[$key];
                }
            }
        }

        return $result;
    }
    
    /**
     * @see FormInterface::sets()
     */
    public function sets(array $pData) 
    {
        $this->_keys = array();
        $this->_fields = array();
        $this->_forms = array();
        
        foreach($pData as $item)
        {
            $this->add($item);
        }
    }
    
    /**
     * @see FormInterface::bind()
     */
    public function bind(array $pData, $pFiltered = true)
    {
        $e = new FormEvent(FormEvent::PRE_BIND, $pData);
        $this->dispatch($e);
        
        $result = $e->getData();
        
        if(null !== $result)
        {
            $pData = $result;
        }
        
        foreach($this->_fields as $field)
        {
            $name = $field->getName();
            
            if(array_key_exists($name, $pData))
            {
                $field->setValue($pData[$name], $pFiltered);
            }
        }
        
        foreach($this->_forms as $form)
        {
            $form->bind($pData, $pFiltered);
        }
        
        $this->dispatch(new FormEvent(FormEvent::BIND, $pData));
    }

    /**
     * @see FormInterface::isEmpty()
     */
    public function isEmpty()
    {
        foreach(array_merge($this->_fields, $this->_forms) as $item)
        {
            if($item->isEmpty())
            {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @see FormInterface::submit()
     */
    public function submit(array $pData = null)
    {
        $e = new FormEvent(FormEvent::PRE_SUBMIT, $pData);
        $this->dispatch($e);
        
        $result = $e->getData();
        
        if(null !== $result)
        {
            $pData = $result;
        }
        
        $this->_errors = array();
        
        if(!empty($pData))
        {
            $this->_submit = true;
            $this->bind($pData, false);
            $this->_submit = false;
        }
        
        foreach($this->_fields as $field)
        {
            if(!$field->isValid())
            {
                $this->_errors[$field->getName()] = $field->errors();
            }
        }
        
        foreach($this->_forms as $form)
        {
            if(!$form->submit())
            {
                $this->_errors[$form->getName()] = $form->errors();
            }
        }
        
        $this->dispatch(new FormEvent(FormEvent::SUBMIT));
        return !$this->hasError();
    }
    
    /**
     * @see FormInterface::values()
     */
    public function values($pFiltered = true)
    {
        $this->dispatch(new FormEvent(FormEvent::PRE_VALUES));
        $values = array();
        
        foreach($this->_fields as $field)
        {
            $values[$field->getName()] = $field->getValue($pFiltered);
        }
        
        foreach($this->_forms as $form)
        {
            $values = array_merge($values, $form->values($pFiltered));
        }
        
        $e = new FormEvent(FormEvent::VALUES, $values);
        $this->dispatch($e);
        
        $result = $e->getData();
        
        if(null !== $result)
        {
            $values = $result;
        }
        
        return $values;
    }
    
    /**
     * @see FormInterface::hasError()
     */
    public function hasError()
    {
        return count($this->_errors) > 0;
    }
    
    /**
     * @see FormInterface::errors()
     */
    public function errors()
    {
        if(!$this->hasError())
        {
            return null;
        }
        
        if(null !== $this->_errorMessage)
        {
            return $this->_errorMessage;
        }
        
        if(count($this->_errors) == 1)
        {
            foreach($this->_errors as $key => $value)
            {
                return array($key => $value);
            }
        }
        
        return $this->_errors;
    }
    
    /**
     * @see FormInterface::reset()
     */
    public function reset(array $pOmit = array())
    {
        $this->_errors = array();
        
        foreach(array_merge($this->_fields, $this->_forms) as $item)
        {
            if(!in_array($item->getName(), $pOmit))
            {
                $item->reset();
            }
        }
        
        $this->dispatch(new FormEvent(FormEvent::RESET));
    }
    
    /**
     * @see Dispatcher::dispatch()
     */
    public function dispatch(Event $pEvent) 
    {
        if($this->_submit && in_array($pEvent->getType(), array(FormEvent::PRE_BIND, FormEvent::BIND)))
        {
            return;
        }
        
        parent::dispatch($pEvent);
    }
}
