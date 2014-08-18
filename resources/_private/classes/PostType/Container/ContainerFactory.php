<?php

namespace Isatech\PostType\Container;

use Isatech\PostType\Item\ItemInterface;

class ContainerFactory 
{
    public static function createContainer(array $pData)
    {
        if(!isset($pData['type']))
        {
            throw new \LogicException('One type of container must be defined.');
        }
        
        $container = new $pData['type']();
        unset($pData['type']);
        
        $camelize = function($pStr)
        {
            return preg_replace(
                '/[^a-z0-9]+/i',
                '', 
                ucwords(
                    str_replace(
                        ['-', '_', '.'], 
                        ' ', 
                        $pStr
                    )
                )
            );
        };
        
        if(isset($pData['items']))
        {
            foreach($pData['items'] as $key => $value)
            {
                if($value instanceof ItemInterface)
                {
                    $item = $value;
                }
                else
                {
                    $value['name'] = $key;
                    $item = static::createItem($value);
                }
                
                $container->addItem($item);
            }
            
            unset($pData['items']);
        }

        foreach($pData as $key => $value)
        {
            $m = 'set' . $camelize($key);

            if(method_exists($metabox, $m))
            {
                if(is_array($value) && isset($value['multiple-args']))
                {
                    unset($value['multiple-args']);
                }
                else
                {
                    $value = [$value];
                }
                
                call_user_func_array([$container, $m], $value);
            }
        }
        
        return $container;
    }

    public static function createItem(array $pData)
    {
        if(!isset($pData['type']))
        {
            throw new \LogicException('One type of container item must be defined.');
        }
        
        if(!isset($pData['name']))
        {
            throw new \LogicException('A container item require a name.');
        }
        
        $item = new $pData['type']($pData['name']);
        unset($pData['type']);
        unset($pData['name']);
        
        $camelize = function($pStr)
        {
            return preg_replace(
                '/[^a-z0-9]+/i',
                '', 
                ucwords(
                    str_replace(
                        ['-', '_', '.'], 
                        ' ', 
                        $pStr
                    )
                )
            );
        };

        foreach($pData as $key => $value)
        {
            $m = 'set' . $camelize($key);

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
}
