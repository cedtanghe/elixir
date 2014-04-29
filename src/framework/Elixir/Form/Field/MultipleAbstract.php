<?php

namespace Elixir\Form\Field;

use Elixir\Form\Field\FieldAbstract;
use Elixir\Form\Field\MultipleInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class MultipleAbstract extends FieldAbstract implements MultipleInterface
{
     /**
     * @see FieldAbstract::setValue()
     */
    public function setValue($pValue, $pFiltered = true)
    {
        $in = function($pValue, array $pData, $pDataUseKeys = false)
        {
            foreach((array)$pValue as $v)
            {
                foreach($pData as $key => $value)
                {
                    if(is_array($value))
                    {
                        if($in($v, $value, $pDataUseKeys))
                        {
                            return true;
                        }
                    }
                    else
                    {
                        if($pDataUseKeys)
                        {
                            if($key == $v)
                            {
                                return true;
                            }
                        }
                        else if($value == $v)
                        {
                            return true;
                        }
                    }
                }
            }
            
            return false;
        };
        
        if(!$in($pValue, $this->getData(), $this->getOption('data-use-keys', false)))
        {
            $pValue = null;
        }
        
        parent::setValue($pValue, $pFiltered);
    }
    
    /**
     * @see MultipleInterface::setData()
     */
    public function setData(array $pValue, $pType = self::DATA_USE_VALUES)
    {
        $this->setOption('data', $pValue);
        $this->setOption('data-use-keys', $pType == self::DATA_USE_KEYS);
    }
    
    /**
     * @see MultipleInterface::getData()
     */
    public function getData()
    {
        return $this->getOption('data', array());
    }
    
    /**
     * @see FieldAbstract::removeOption()
     * @throws \LogicException
     */
    public function removeOption($pKey)
    {
        if(in_array($pKey, array('data', 'data-use-keys')))
        {
            throw new \LogicException(sprintf('You can not delete the option "%s".', $pKey));
        }
        
        parent::removeOption($pKey);
    }
    
    /**
     * @see FieldAbstract::setOptions()
     */
    public function setOptions(array $pData)
    {
        $data = $this->getData();
        $type = $this->getOption('data-use-keys');

        parent::setOptions($pData);

        if(!$this->hasOption('data'))
        {
            $this->setOption('data', $data);
        }

        if(!$this->hasOption('data-use-keys'))
        {
            $this->setOption('data-use-keys', $type);
        }
    }
}
