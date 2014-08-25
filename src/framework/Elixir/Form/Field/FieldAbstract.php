<?php

namespace Elixir\Form\Field;

use Elixir\Dispatcher\Dispatcher;
use Elixir\Facade\Filter;
use Elixir\Facade\Validator;
use Elixir\Filter\FilterInterface;
use Elixir\Form\Field\FieldEvent;
use Elixir\Form\Field\FieldInterface;
use Elixir\Form\FormInterface;
use Elixir\Validator\ValidatorInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class FieldAbstract extends Dispatcher implements FieldInterface
{
    /**
     * @var string|callable 
     */
    protected $_helper;
    
    /**
     * @var FormInterface
     */
    protected $_parent;
    
    /**
     * @var string
     */
    protected $_errorMessage;
    
    /**
     * @var array
     */
    protected $_attributes = [];
    
    /**
     * @var array
     */
    protected $_options = [];
    
    /**
     * @var boolean
     */
    protected $_required = false;
    
    /**
     * @var mixed
     */
    protected $_value;
    
    /**
     * @var array
     */
    protected $_filters = [];
    
    /**
     * @var array
     */
    protected $_validators = [];
    
    /**
     * @var boolean
     */
    protected $_errorBreak = true;
    
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
            $this->setAttribute('name', $pName);
        }
    }
    
    /**
     * @see FieldInterface::getName()
     */
    public function setName($pValue)
    {
        $this->setAttribute('name', $pValue);
    }
    
    /**
     * @see FieldInterface::getName()
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }
    
    /**
     * @see FieldInterface::setHelper()
     */
    public function setHelper($pValue)
    {
        $this->_helper = $pValue;
    }
    
    /**
     * @see FieldInterface::getHelper()
     */
    public function getHelper()
    {
        return $this->_helper;
    }
    
    /**
     * @see FieldInterface::setParent()
     */
    public function setParent(FormInterface $pValue)
    {
        $this->_parent = $pValue;
    }
    
    /**
     * @see FieldInterface::getParent()
     */
    public function getParent()
    {
        return $this->_parent;
    }
    
    /**
     * @see FieldInterface::setLabel()
     */
    public function setLabel($pValue)
    {
        $this->setOption('label', $pValue);
    }
    
    /**
     * @see FieldInterface::getLabel()
     */
    public function getLabel()
    {
        return $this->getOption('label');
    }
    
    /**
     * @see FieldInterface::setErrorMessage()
     */
    public function setErrorMessage($pValue)
    {
        $this->_errorMessage = $pValue;
    }
    
    /**
     * @see FieldInterface::getErrorMessage()
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }
    
    /**
     * @see FieldInterface::setRequired()
     */
    public function setRequired($pValue)
    {
        $this->_required = $pValue;
    }
    
    /**
     * @see FieldInterface::isRequired()
     */
    public function isRequired()
    {
        return $this->_required;
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
        if($pKey == 'required')
        {
            $this->setRequired((bool)$pValue);
        }
        
        if($pKey == 'name')
        {
            $name = $this->getAttribute('name');
            
            if($pValue != $name)
            {
                if(null !== $this->getParent())
                {
                    throw new \LogicException('You can not redefine the name of the form field if it already has a parent.');
                }
            }
        }
        
        $this->_attributes[$pKey] = $pValue;
    }
    
    /**
     * @param string $pKey
     * @throws \LogicException
     */
    public function removeAttribute($pKey)
    {
        if($pKey == 'name')
        {
            throw new \LogicException('You can not delete the name of an field.');
        }
        else if($pKey == 'required')
        {
            $this->setRequired(false);
        }
        
        unset($this->_attributes[$pKey]);
    }
    
    /**
     * @see FieldInterface::getAttributes()
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }
    
    /**
     * @see FieldInterface::setAttributes()
     */
    public function setAttributes(array $pData) 
    {
        $pData['name'] = $this->getName();
        $this->_attributes = [];
        
        foreach($pData as $key => $value)
        {
            $this->setAttribute($key, $value);
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
     * @see FieldInterface::getOptions()
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
    /**
     * @see FieldInterface::setOptions()
     */
    public function setOptions(array $pData) 
    {
        $label = $this->getLabel();
        $this->_options = [];
        
        foreach($pData as $key => $value)
        {
            $this->setOption($key, $value);
        }
        
        if(null !== $label && !$this->hasOption('label'))
        {
            $this->setLabel($label);
        }
    }
    
    /**
     * @see FieldInterface::setErrorBreak()
     */
    public function setErrorBreak($pValue)
    {
        $this->_errorBreak = $pValue;
    }
    
    /**
     * @see FieldInterface::isErrorBreak()
     */
    public function isErrorBreak()
    {
        return $this->_errorBreak;
    }

    /**
     * @param ValidatorInterface|callable|string $pValidator
     * @param array $pOptions
     */
    public function addValidator($pValidator, array $pOptions = [])
    {
        $this->_validators[] = ['validator' => $pValidator, 'options' => $pOptions];
    }

    /**
     * @see FieldInterface::getValidators()
     */
    public function getValidators()
    {
        return $this->_validators;
    }
    
    /**
     * @see FieldInterface::setValidators()
     */
    public function setValidators(array $pData)
    {
        $this->_validators = [];
        
        foreach($pData as $data)
        {
            $validator = $data;
            $options = [];
            
            if(is_array($data))
            {
                $validator = $data['validator'];
                
                if(isset($data['options']))
                {
                    $options = $data['options'];
                }
            }
            
            $this->addValidator($validator, $options);
        }
    }

    /**
     * @param FilterInterface|callable|string $pFilter
     * @param array $pOptions
     * @param integer $pType
     */
    public function addFilter($pFilter, array $pOptions = [], $pType = self::FILTER_OUT)
    {
        $this->_filters[] = ['filter' => $pFilter, 'options' => $pOptions, 'type' => $pType];
    }
    
    /**
     * @see FieldInterface::getFilters()
     */
    public function getFilters()
    {
        return $this->_filters;
    }
    
    /**
     * @see FieldInterface::setFilters()
     */
    public function setFilters(array $pData)
    {
        $this->_filters = [];
        
        foreach($pData as $data)
        {
            $filter = $data;
            $options = [];
            $type = self::FILTER_OUT;
            
            if(is_array($data))
            {
                $filter = $data['filter'];
                
                if(isset($data['options']))
                {
                    $options = $data['options'];
                }
                
                if(isset($data['type']))
                {
                    $type = $data['type'];
                }
            }
            
            $this->addFilter($filter, $options, $type);
        }
    }
    
    /**
     * @see FieldInterface::setValue()
     */
    public function setValue($pValue, $pFiltered = true)
    {
        if($pFiltered)
        {
            foreach($this->_filters as $data)
            {
                if(($data['type'] & self::FILTER_IN) == self::FILTER_IN)
                {
                    if($data['filter'] instanceof FilterInterface)
                    {
                        $pValue = $data['filter']->filter($pValue, $data['options']);
                    }
                    else if(is_callable($data['filter']))
                    {
                        $pValue = call_user_func_array($data['filter'], [$pValue, $data['options']]);
                    }
                    else
                    {
                        $pValue = Filter::filter($data['filter'], $pValue, $data['options']);
                    }
                }
            }
        }
        
        $this->_value = $pValue;
    }
    
    /**
     * @see FieldInterface::getValue()
     */
    public function getValue($pFiltered = true)
    {
        $value = $this->_value;
        
        if($pFiltered)
        {
            foreach($this->_filters as $data)
            {
                if(($data['type'] & self::FILTER_OUT) == self::FILTER_OUT)
                {
                    if($data['filter'] instanceof FilterInterface)
                    {
                        $value = $data['filter']->filter($value, $data['options']);
                    }
                    else if(is_callable($data['filter']))
                    {
                        $value = call_user_func_array($data['filter'], [$value, $data['options']]);
                    }
                    else
                    {
                        $value = $data['filter']->filter($value, $data['options']);
                    }
                }
            }
        }
        
        return $value;
    }
    
    /**
     * @see FieldInterface::isEligible()
     */
    public function isEligible()
    {
        return $this->_required || !$this->isEmpty();
    }
    
    /**
     * @see FieldInterface::isEmpty()
     */
    public function isEmpty()
    {
        $value = $this->getValue(false);
        
        if(is_array($value))
        {
            return count($value) == 0;
        }
        
        $value = trim($value);
        return empty($value);
    }
    
    /**
     * @see FieldInterface::prepare()
     */
    public function prepare()
    {
        if($this->_prepare)
        {
            return;
        }
        
        $this->_prepare = true;
    }
    
    /**
     * @see FieldInterface::isPrepared()
     */
    public function isPrepared()
    {
        return $this->_prepare;
    }
    
    /**
     * @see FieldInterface::isValid()
     */
    public function isValid($pValue = null)
    {
        $this->_errors = [];
        
        if(null !== $pValue)
        {
            $this->_value = $pValue;
        }
        
        $event = new FieldEvent(FieldEvent::PRE_VALIDATION, $this->_value);
        $this->dispatch($event);
        $this->_value = $event->getValue();
        
        if($this->isEligible())
        {
            foreach($this->_validators as $data)
            {
                if($data['validator'] instanceof ValidatorInterface)
                {
                    $valid = $data['validator']->isValid($this->_value, $data['options']);
                    $errors = $data['validator']->errors();
                }
                else if(is_callable($data['validator']))
                {
                    $result = call_user_func_array(
                        $data['validator'], 
                        [
                            $this->_value, 
                            array_merge($data['options'], ['with-errors' => true])
                        ]
                    );
                    
                    if(is_array($result))
                    {
                        $valid = $result['valid'];
                        $errors = $result['errors'];
                    }
                    else
                    {
                        $valid = $result;
                        $errors = $valid ? [] : [isset($data['options']['error']) ? $data['options']['error'] : false];
                    }
                }
                else
                {
                    $result = Validator::valid(
                        $data['validator'], 
                        $this->_value, 
                        array_merge($data['options'], ['with-errors' => true])
                    );
                    
                    $valid = $result['valid'];
                    $errors = $result['errors'];
                }
                
                if(!$valid)
                {
                    $this->_errors = array_unique(array_merge($this->_errors, (array)$errors));
                    
                    if($this->_errorBreak)
                    {
                        break;
                    }
                }
            }
        }
        
        return !$this->hasError();
    }
    
    /**
     * @see FieldInterface::hasError()
     */
    public function hasError()
    {
        return count($this->_errors) > 0;
    }
    
    /**
     * @see FieldInterface::errors()
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
        
        return count($this->_errors) == 1 ? $this->_errors[0] : $this->_errors;
    }
    
    /**
     * @see FieldInterface::reset()
     */
    public function reset()
    {
        $this->_errors = [];
        $this->_value = null;
    }
}