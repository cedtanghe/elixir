<?php

namespace Elixir\Form\Field;

use Elixir\Facade\Validator;
use Elixir\Form\Field\FieldAbstract;
use Elixir\Form\Field\FieldEvent;
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
     * @var CSRFValidator|callable|string
     */
    protected $_CSRFValidator;
    
    /**
     * @var array
     */
    protected $_CSRFValidatorOptions = [];

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
     * @param CSRFValidator|callable|string $pValidator
     * @param array $pOptions
     */
    public function setCSRFValidator($pValidator, array $pOptions = [])
    {
        $this->_CSRFValidator = $pValidator;
        
        if(count($pOptions) > 0)
        {
            $this->setCSRFValidatorOptions($pOptions);
        }
    }
    
    /**
     * @return mixed
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
    public function setCSRFValidatorOptions(array $pValue = [])
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
     */
    public function addValidator($pValidator, array $pOptions = [])
    {
        $this->setCSRFValidator($pValidator);
        $this->setCSRFValidatorOptions($pOptions);
    }
    
    /**
     * @see FieldAbstract::addFilter()
     * @throws \LogicException
     */
    public function addFilter($pFilter, array $pOptions = [], $pType = self::FILTER_OUT)
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
            $params = [$this->getName()];
            
            if(isset($this->_CSRFValidatorOptions['time']))
            {
                $params[] = $this->_CSRFValidatorOptions['time'];
            }
            
            $this->_value = call_user_func_array([$this->getCSRFValidator(), 'createToken'], $params);
            parent::prepare();
        }
    }
    
    /**
     * @see FieldAbstract::isValid()
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
        
        $validator = $this->getCSRFValidator();
        $options = $this->getCSRFValidatorOptions();
        
        if($validator instanceof ValidatorInterface)
        {
            $valid = $validator->isValid($this->getName(), $options);
            $errors = $validator->errors();
        }
        else if(is_callable($validator))
        {
            $result = call_user_func_array(
                $validator, 
                [
                    $this->getName(), 
                    array_merge($options, ['with-errors' => true])
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
                $validator, 
                $this->getName(), 
                array_merge($options, ['with-errors' => true])
            );

            $valid = $result['valid'];
            $errors = $result['errors'];
        }
        
        if(!$valid)
        {
            $this->_errors = (array)$errors;
        }
        
        return !$this->hasError();
    }
}
