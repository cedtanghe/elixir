<?php

namespace Elixir\Validator;

use Elixir\Facade\Validator;
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
     * @param ValidatorInterface|callable|string $pValidator
     * @param array $pOptions
     */
    public function addValidator($pValidator, array $pOptions = [])
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
            if($data['validator'] instanceof ValidatorInterface)
            {
                $valid = $data['validator']->isValid($pContent, $data['options']);
                $errors = $data['validator']->errors();
            }
            else if(is_callable($data['validator']))
            {
                $result = call_user_func_array(
                    $data['validator'], 
                    [
                        $pContent, 
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
                    $pContent, 
                    array_merge($data['options'], ['with-errors' => true])
                );

                $valid = $result['valid'];
                $errors = $result['errors'];
            }
            
            if(!$valid)
            {
                $this->_errors = array_merge($this->_errors, (array)$errors);
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