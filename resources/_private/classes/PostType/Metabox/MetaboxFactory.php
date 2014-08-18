<?php

namespace Isatech\PostType\Metabox;

use Isatech\PostType\Item\ItemInterface;

class MetaboxFactory 
{
    public static function createSimpleContainer(array $pData)
    {
        if(!isset($pData['type']))
        {
            throw new \LogicException('One type of simple container must be defined.');
        }
        
        $simpleContainer = new $pData['type']();
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
                
                $simpleContainer->addItem($item);
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
                
                call_user_func_array([$simpleContainer, $m], $value);
            }
        }
        
        return $simpleContainer;
    }
    
    public static function createMetabox(array $pData)
    {
        if(!isset($pData['type']))
        {
            throw new \LogicException('One type of metabox must be defined.');
        }
        
        if(!isset($pData['identifier']))
        {
            throw new \LogicException('A metabox require a identifier.');
        }
        
        $metabox = new $pData['type']($pData['identifier']);
        unset($pData['type']);
        unset($pData['identifier']);
        
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
                
                $metabox->addItem($item);
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
                
                call_user_func_array([$metabox, $m], $value);
            }
        }
        
        return $metabox;
    }

    public static function createItem(array $pData)
    {
        if(!isset($pData['type']))
        {
            throw new \LogicException('One type of metabox item must be defined.');
        }
        
        if(!isset($pData['name']))
        {
            throw new \LogicException('A metabox item require a name.');
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
