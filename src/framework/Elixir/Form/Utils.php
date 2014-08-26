<?php

namespace Elixir\Form;

use Elixir\Form\FormInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Utils
{
    /**
     * @var string
     */
    const PREFIX_SEPARATOR = '@';
    
   /**
    * @param string $pPrefix
    * @param string $pValue
    * @return string
    */
    public static function prefix($pPrefix, $pValue)
    {
        return $pPrefix . self::PREFIX_SEPARATOR . $pValue;
    }
    
    /**
     * @param array $pData
     * @param string $pPrefix
     * @param boolean $pRemovePrefix
     * @return array
     */
    public static function getDataByPrefix(array $pData, $pPrefix, $pRemovePrefix = true)
    {
        $result = [];
        
        foreach($pData as $key => &$value)
        {
            if(is_array($value))
            {
                $result = array_merge($result, static::getDataByPrefix($pPrefix, $value, false));
            }
            else
            {
                $pos = strpos($key, $pPrefix . self::PREFIX_SEPARATOR);

                if(false !== $pos)
                {
                    $result[$key] = $value;
                }
            }
        }
        
        return $pRemovePrefix ? static::removePrefix($result) : $result;
    }
    
    /**
     * @param mixed $pData
     * @return mixed
     */
    public static function removePrefix($pData)
    {
        $process = function($pName)
        {
            $i = 0;

            do
            {
                $pos = strpos($pName, self::PREFIX_SEPARATOR, $i);

                if(false !== $pos)
                {
                    $i = $pos;
                }
            }
            while(false !== $pos);

            if($i > 0)
            {
                $pName = substr($pName, 0, $i);
            }
            
            return $pName;
        };
        
        if(is_array($pData))
        {
            $keys = array_keys($pData);
            $values = array_values($pData);

            foreach($keys as &$key)
            {
                $key = $process($key);
            }
            
            foreach($values as &$value)
            {
                if(is_array($value))
                {
                    $value = static::removePrefix($value);
                }
            }

            return array_combine($keys, $values);
        }
        
        return $process($pData);
    }
    
    /**
     * @param FormInterface $pForm
     * @param string $pPrefix
     */
    public static function prefixForm(FormInterface $pForm, $pPrefix = null)
    {
        foreach($pForm->gets(FormInterface::ALL_ITEMS) as $item)
        {
            if(null !== $pPrefix)
            {
                $item->setName(static::prefix($pPrefix, $item->getName()));
            }
            
            if($item instanceof FormInterface)
            {
                static::prefixForm($item, $item->getName());
            }
        }
    }
    
    /**
     * @param array $pData
     * @param string $pIdentifier
     * @param string|array $pWording
     * @param string $pMask
     * @return array
     */
    public static function createOptions(array $pData, $pIdentifier, $pWording = null, $pMask = '%s')
    {
        $options = [];
        
        foreach($pData as $data)
        {
            if(!is_array($data))
            {
                if(method_exists($data, 'export'))
                {
                    $data = $data->export();
                }
                else
                {
                    $data = get_object_vars($data);
                }
            }
            
            if(null !== $pWording)
            {
                if(is_array($pWording))
                {
                    $value = [];
                    
                    foreach($pWording as $word)
                    {
                        $value[] = $data[$word];
                    }
                    
                    $options[$data[$pIdentifier]] = call_user_func_array('sprintf', array_merge([$pMask], $value));
                }
                else
                {
                    $options[$data[$pIdentifier]] = $data[$pWording];
                }
            }
            else
            {
                $options[] = $data[$pIdentifier];
            }
        }
       
        return $options;
    }
}
