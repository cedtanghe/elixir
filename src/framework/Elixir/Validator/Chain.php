<?php

namespace Elixir\Validator;

use Elixir\Validator\ValidatorAbstract;
use Elixir\Validator\ValidatorInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Chain extends ValidatorAbstract
{
    /**
     * @var array
     */
    protected $_validators = [];
    
    /**
     * @param ValidatorInterface $pValidator
     * @param array $pOptions
     */
    public function addValidator(ValidatorInterface $pValidator, array $pOptions = [])
    {
        $this->_validators[] = ['validator' => $pValidator, 'options' => $pOptions];
    }
    
    /**
     * @return array
     */
    public function getValidators()
    {
        return $this->_validators;
    }
    
    /**
     * @param array $pData
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
     * @see ValidatorInterface::isValid()
     */
    public function isValid($pContent, array $pOptions = []) 
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $this->_errors = [];
        
        foreach($this->_validators as $data)
        {
            if(!$data['validator']->isValid($pContent, $data['options']))
            {
                $this->_errors = array_merge($this->_errors, (array)$data['validator']->errors());
            }
        }
        
        if($this->hasError())
        {
            if(isset($pOptions['error']))
            {
                $this->_errors = (array)$pOptions['error'];
            }
            
            return false;
        }
        
        return true;
    }
}