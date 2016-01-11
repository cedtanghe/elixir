<?php

namespace Elixir\Form;

use Elixir\Dispatcher\Dispatcher;
use Elixir\Dispatcher\Event;
use Elixir\Form\Extension\ExtensionInterface;
use Elixir\Form\Field\FieldInterface;
use Elixir\Form\Field\FileInterface;
use Elixir\Form\Field\Input;
use Elixir\Form\FormEvent;
use Elixir\Form\FormInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Form extends Dispatcher implements FormInterface
{
    /**
     * @var callable|null
     */
    protected $_helper;
    
    /**
     * @var boolean
     */
    protected $_submit = false;
    
    /**
     * @var boolean
     */
    protected $_submited = false;
    
    /**
     * @var FormInterface
     */
    protected $_parent;
    
    /**
     * @var array
     */
    protected $_keys = [];
    
    /**
     * @var array
     */
    protected $_fields = [];
    
    /**
     * @var array
     */
    protected $_forms = [];
    
    /**
     * @var array
     */
    protected $_attributes = ['method' => self::POST, 'action' => ''];
    
    /**
     * @var array
     */
    protected $_options = [];
    
    /**
     * @var string
     */
    protected $_errorMessage;
    
    /**
     * @var array
     */
    protected $_errors = [];
    
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
        
        if($this->getAttribute('enctype') == FormInterface::ENCTYPE_MULTIPART)
        {
            $this->_parent->setAttributes(
                array_merge(
                    $this->_parent->getAttributes(),
                    ['enctype' => FormInterface::ENCTYPE_MULTIPART])
            );
        }
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
     * @param string $pValue
     */
    public function setMethod($pValue)
    {
        $this->setAttribute('method', $pValue);
    }
    
    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->getAttribute('method');
    }
    
    /**
     * @param string $pValue
     */
    public function setAction($pValue)
    {
        $this->setAttribute('action', $pValue);
    }
    
    /**
     * @return string
     */
    public function getAction()
    {
        return $this->getAttribute('action');
    }
    
    /**
     * @see FormInterface::setHelper()
     */
    public function setHelper($pValue)
    {
        $this->_helper = $pValue;
    }
    
    /**
     * @see FormInterface::getHelper()
     */
    public function getHelper()
    {
        return $this->_helper;
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
     */
    public function setAttribute($pKey, $pValue)
    {
        if($pKey == 'method')
        {
            if(in_array(strtolower($pValue), [self::PUT, self::DELETE]))
            {
                $input = $this->get(self::METHOD_FIELD, true, null);
                
                if(null === $input)
                {
                    $input = new Input(self::METHOD_FIELD);
                    $input->setType(Input::HIDDEN);
                    
                    $this->add($input);
                }
                
                $input->setValue($pValue);
                $pValue = self::POST;
            }
            else
            {
                $this->remove(self::METHOD_FIELD);
            }
            
            $this->_attributes[$pKey] = $pValue;
        }
        else if($pKey == 'name')
        {
            $name = $this->getAttribute('name');
            
            if($pValue != $name)
            {
                $this->_attributes[$pKey] = $pValue;
                
                if(null !== $name)
                {
                    $this->dispatch(new FormEvent(FormEvent::RENAME));
                }
            }
        }
        else if($pKey == 'enctype')
        {
            $this->_attributes[$pKey] = $pValue;
            
            if(null !== $this->_parent && $pValue == FormInterface::ENCTYPE_MULTIPART)
            {
                $this->_parent->setAttributes(
                    array_merge(
                        $this->_parent->getAttributes(),
                        ['enctype' => $pValue])
                );
            }
        }
        else
        {
            $this->_attributes[$pKey] = $pValue;
        }
    }
    
    /**
     * @param string $pKey
     * @throws \LogicException
     */
    public function removeAttribute($pKey)
    {
        if($pKey == 'method')
        {
            $this->remove(self::METHOD_FIELD);
        }
        else if($pKey == 'name' && null !== $this->getParent())
        {
            throw new \LogicException('You can not delete the name of an form if it already is a form child.');
        }
        
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
        $this->_attributes = [];
        
        foreach($pData as $key => $value)
        {
            $this->setAttribute($key, $value);
        }
        
        if(null !== $name && !$this->hasAttribute('name'))
        {
            $this->setName($name);
        }
        
        if(!$this->hasAttribute('method'))
        {
            $this->remove(self::METHOD_FIELD);
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
        $this->_options = [];
        
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
        $pItem->addListener(FormEvent::RENAME, [$this, 'onRenameItem']);
    }
    
    /**
     * @internal
     * @param FormEvent $e
     * @throws \LogicException
     */
    public function onRenameItem(FormEvent $e)
    {
        $items = array_merge($this->_fields, $this->_forms);
        
        foreach($items as $key => $value)
        {
            if($e->getTarget() === $value)
            {
                $name = $value->getName();
                
                if(isset($items[$name]))
                {
                    throw new \LogicException(sprintf('An item that already has the name "%s" already exists.', $name));
                }
                
                $pos = array_search($key, $this->_keys);
                array_splice($this->_keys, $pos, 1, $name);
                
                if($value instanceof FormInterface)
                {
                    unset($this->_forms[$key]);
                    $this->_forms[$name] = $value;
                }
                else
                {
                    unset($this->_fields[$key]);
                    $this->_fields[$name] = $value;
                }
            }
        }
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
            
            if(isset($this->_fields[$pName]))
            {
                $this->_fields[$pName]->removeListener(FormEvent::RENAME, [$this, 'onRenameItem']);
                unset($this->_fields[$pName]);
            }
            else if(isset($this->_forms[$pName]))
            {
                $this->_forms[$pName]->removeListener(FormEvent::RENAME, [$this, 'onRenameItem']);
                unset($this->_forms[$pName]);
            }
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
        $result = [];

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
        $this->_keys = [];
        $this->_fields = [];
        $this->_forms = [];
        
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
        $this->_submited = false;
        
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
     * @return boolean
     */
    public function isSubmited()
    {
        return $this->_submited;
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
        
        $this->_errors = [];
        
        if(!empty($pData))
        {
            $this->_submit = true;
            $this->bind($pData, true);
            $this->_submit = false;
        }
        
        $this->dispatch(new FormEvent(FormEvent::PRE_SUBMIT_VALIDATION));
        
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
        
        $this->_submited = true;
        
        $this->dispatch(new FormEvent(FormEvent::SUBMIT));
        return !$this->hasError();
    }
    
    /**
     * @return boolean
     */
    public function receive()
    {
        $receive = true;
        
        foreach($this->gets(self::ALL_FIELDS) as $field)
        {
            if($field instanceof FileInterface)
            {
                if(!$field->receive())
                {
                    $receive = false;
                }
            }
        }
        
        return $receive;
    }
    
    /**
     * @param array $pMembers
     * @param array $pOmitMembers
     * @param array $pData
     * @return boolean
     */
    public function required(array $pMembers = [], array $pOmitMembers = [], array $pData = null)
    {
        if(null !== $pData || !$this->_submited)
        {
            $this->submit($pData);
        }
        
        foreach($this->_keys as $key)
        {
            if(in_array($key, $pOmitMembers))
            {
                continue;
            }
            else if(count($pMembers) > 0 && !in_array($key, $pMembers))
            {
                continue;
            }
            
            if(isset($this->_errors[$key]))
            {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * @see FormInterface::values()
     */
    public function values($pFiltered = true)
    {
        $this->dispatch(new FormEvent(FormEvent::PRE_VALUES));
        $values = [];
        
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
                return [$key => $value];
            }
        }
        
        return $this->_errors;
    }
    
    /**
     * @see FormInterface::bindErrors()
     */
    public function bindErrors(array $pData)
    {
        foreach($pData as $key => $value)
        {
            $item = $this->get($key);
            
            if ($item)
            {
                $item->bindErrors($value);
                $this->_errors[$item->getName()] = $item->errors();
            }
        }
    }
    
    /**
     * @see FormInterface::reset()
     */
    public function reset(array $pOmit = [])
    {
        $this->_errors = [];
        $this->_submited = false;
        
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
        if($this->_submit && in_array($pEvent->getType(), [FormEvent::PRE_BIND, FormEvent::BIND]))
        {
            return;
        }
        
        parent::dispatch($pEvent);
    }
}
