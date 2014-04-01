<?php

namespace Elixir\Form\Field;

use Elixir\Filter\FilterInterface;
use Elixir\Validator\CSRF as CSRFValidator;
use Elixir\Validator\ValidatorInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class CSRF extends FieldAbstract
{
    /**
     * @var string
     */
    const HIDDEN = 'hidden';
    
    /**
     * @var \Elixir\Validator\CSRF
     */
    protected $_CSRFValidator;
    
    /**
     * @var array
     */
    protected $_CSRFValidatorOptions = array();

    /**
     * @see FieldAbstract::__construct()
     */
    public function __construct($pName = null)
    {
        parent::__construct($pName);
        
        $this->_helper = 'CSRF';
        $this->setAttribute('type', self::HIDDEN);
    }
    
    /**
     * @see FieldAbstract::setRequired()
     */
    public function setRequired($pValue) 
    {
        parent::setRequired(true);
    }
    
    /**
     * @param \Elixir\Validator\CSRF $pValidator
     * @param array $pOptions
     */
    public function setCSRFValidator(CSRFValidator $pValidator, array $pOptions = array())
    {
        $this->_CSRFValidator = $pValidator;
        
        if(count($pOptions) > 0)
        {
            $this->setCSRFValidatorOptions($pOptions);
        }
    }
    
    /**
     * @return \Elixir\Validator\CSRF
     */
    public function getCSRFValidator()
    {
        if(null === $this->_CSRFValidator)
        {
            $this->_CSRFValidator = new CSRFValidator();
        }
        
        return $this->_CSRFValidator;
    }
    
    /**
     * @param array $pOptions
     */
    public function setCSRFValidatorOptions(array $pValue = array())
    {
        $this->_CSRFValidatorOptions = $pValue;
    }
    
    /**
     * @return array
     */
    public function getCSRFValidatorOptions()
    {
        return $this->_CSRFValidatorOptions;
    }

    /**
     * @see FieldAbstract::setAttribute()
     */
    public function setAttribute($pKey, $pValue) 
    {
        if($pKey == 'type')
        {
            $pValue = self::HIDDEN;
        }
        
        parent::setAttribute($pKey, $pValue);
    }

    /**
     * @see FieldAbstract::removeAttribute()
     * @throws \LogicException
     */
    public function removeAttribute($pKey)
    {
        if($pKey == 'type')
        {
            throw new \LogicException('You can not delete the option "type".');
        }
        
        parent::removeAttribute($pKey);
    }
    
    /**
     * @see FieldAbstract::setAttributes()
     */
    public function setAttributes(array $pData)
    {
        $pData['type'] = self::HIDDEN;
        parent::setAttributes($pData);
    }
    
    /**
     * @see FieldAbstract::addValidator()
     * @throws \LogicException
     */
    public function addValidator(ValidatorInterface $pValidator, array $pOptions = array())
    {
        if($pValidator instanceof CSRFValidator)
        {
            $this->setCSRFValidator($pValidator);
            $this->setCSRFValidatorOptions($pOptions);
            return;
        }
        
        throw new \LogicException('CSRF field accepts only validator type "\Elixir\Validator\CSRF".');
    }
    
    /**
     * @see FieldAbstract::addFilter()
     * @throws \LogicException
     */
    public function addFilter(FilterInterface $pFilter, array $pOptions = array(), $pType = self::FILTER_OUT)
    {
        throw new \LogicException('No filter available for CSRF field.');
    }
    
    /**
     * @see FieldAbstract::prepare()
     */
    public function prepare()
    {
        if(!$this->isPrepared())
        {
            $params = array($this->getName());
            
            if(isset($this->_CSRFValidatorOptions['time']))
            {
                $params[] = $this->_CSRFValidatorOptions['time'];
            }
            
            $this->_value = call_user_func_array(array($this->getCSRFValidator(), 'createToken'), $params);
            parent::prepare();
        }
    }
    
    /**
     * @see FieldAbstract::isValid()
     */
    public function isValid($pValue = null) 
    {
        $this->_errors = array();
        
        if(null !== $pValue)
        {
            $this->_value = $pValue;
        }
        
        $event = new FieldEvent(FieldEvent::PRE_VALIDATION, $this->_value);
        $this->dispatch($event);
        $this->_value = $event->getValue();
        
        if(!$this->getCSRFValidator()->isValid($this->getName(), $this->getCSRFValidatorOptions()))
        {
            $this->_errors = (array)$this->getCSRFValidator()->errors();
        }
        
        return !$this->hasError();
    }
}