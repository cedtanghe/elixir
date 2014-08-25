<?php

namespace Elixir\Form;

use Elixir\Form\Field\FieldInterface;
use Elixir\Form\FormInterface;
use Elixir\Util\Str;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class FormFactory
{
    /**
     * @var string
     */
    const DEFAULT_FORM = '\Elixir\Form\Form';
    
    /**
     * @param array $pData
     * @return FormInterface
     */
    public static function createForm(array $pData)
    {
        $class = isset($pData['type']) ? $pData['type'] : self::DEFAULT_FORM;
        $form = new $class();
        
        // Name
        if(isset($pData['name']))
        {
            $form->setName($pData['name']);
            unset($pData['name']);
        }
        
        // Error message
        if(isset($pData['errorMessage']))
        {
            $form->setErrorMessage($pData['errorMessage']);
            unset($pData['errorMessage']);
        }
        
        // Attributes
        if(isset($pData['attributes']))
        {
            $form->setAttributes($pData['attributes']);
            unset($pData['attributes']);
        }
        
        // Options
        if(isset($pData['options']))
        {
            $form->setOptions($pData['options']);
            unset($pData['options']);
        }
        
        // Helper
        if(isset($pData['helper']))
        {
            $form->setHelper($pData['helper']);
            unset($pData['helper']);
        }
        
        // Events
        if(isset($pData['events']))
        {
            foreach($pData['events'] as $event => $callable)
            {
                $form->addListener($event, $callable);
            }
        }
        
        // Items
        if(isset($pData['items']))
        {
            foreach($pData['items'] as $key => $value)
            {
                if($value instanceof FieldInterface || $value instanceof FormInterface)
                {
                    $item = $value;
                }
                else
                {
                    $value['name'] = $key;
                    $item = static::createField($value);
                }
                
                $form->add($item);
            }
            
            unset($pData['items']);
        }
        
        // Values
        if(isset($pData['values']))
        {
            $form->bind($pData['values']);
            unset($pData['values']);
        }
        
        // Others methods
        foreach($pData as $key => $value)
        {
            $m = 'set' . Str::camelize($key);
            
            if(method_exists($form, $m))
            {
                if(is_array($value) && isset($value['multiple-args']))
                {
                    unset($value['multiple-args']);
                }
                else
                {
                    $value = [$value];
                }

                call_user_func_array([$form, $m], $value);
            }
        }
        
        return $form;
    }

    /**
     * @param array $pData
     * @return FieldInterface
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public static function createField(array $pData)
    {
        if(!isset($pData['name']))
        {
            throw new \LogicException('A form item require a name.');
        }
        
        if(isset($pData['type']))
        {
            if('\\' . ltrim($pData['type'], '\\') == self::DEFAULT_FORM)
            {
                return static::createForm($pData);
            }
            
            $item = new $pData['type']();
            unset($pData['type']);
            
            // Name
            $item->setName($pData['name']);
            unset($pData['name']);
            
            // Label
            if(isset($pData['label']))
            {
                $item->setLabel($pData['label']);
                unset($pData['label']);
            }
            
            // Required
            if(isset($pData['required']))
            {
                $item->setRequired($pData['required']);
                unset($pData['required']);
            }
            
            // Helper
            if(isset($pData['helper']))
            {
                $item->setHelper($pData['helper']);
                unset($pData['helper']);
            }
            
            // Error message
            if(isset($pData['errorMessage']))
            {
                $item->setErrorMessage($pData['errorMessage']);
                unset($pData['errorMessage']);
            }
            
            // Attributes
            if(isset($pData['attributes']))
            {
                $item->setAttributes($pData['attributes']);
                unset($pData['attributes']);
            }

            // Options
            if(isset($pData['options']))
            {
                $item->setOptions($pData['options']);
                unset($pData['options']);
            }
            
            // Validators
            if(isset($pData['validators']))
            {
                $item->setValidators($pData['validators']);
                unset($pData['validators']);
            }
            
            // Filters
            if(isset($pData['filters']))
            {
                $item->setFilters($pData['filters']);
                unset($pData['filters']);
            }
            
            // Data
            if(isset($pData['data']))
            {
                $dataUseKeys = isset($pData['data-use-keys']) ? $pData['data-use-keys'] : false;
                $item->setData($pData['data'], $dataUseKeys);
                
                unset($pData['data']);
            }
            
            // Value
            if(isset($pData['value']))
            {
                $item->setValue($pData['value']);
                unset($pData['value']);
            }
            
            // Events
            if(isset($pData['events']))
            {
                foreach($pData['events'] as $event => $callable)
                {
                    $item->addListener($event, $callable);
                }
            }
            
            // Others methods
            foreach($pData as $key => $value)
            {
                $m = 'set' . Str::camelize($key);

                if(method_exists($item, $m))
                {
                    if(is_array($value) && isset($value['multiple-args']))
                    {
                        unset($value['multiple-args']);
                    }
                    else
                    {
                        $value = [$value];
                    }

                    call_user_func_array([$item, $m], $value);
                }
            }
            
            return $item;
        }
        else if(isset($pData['items']))
        {
            return static::createForm($pData);
        }
        
        throw new \InvalidArgumentException('Unable to determine the type of item associated with this form.');
    }
}
