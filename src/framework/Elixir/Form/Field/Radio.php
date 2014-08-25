<?php

namespace Elixir\Form\Field;

use Elixir\Form\Field\MultipleAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Radio extends MultipleAbstract
{
    /**
     * @var string
     */
    const RADIO = 'radio';
    
    /**
     * @see MultipleAbstract::__construct()
     */
    public function __construct($pName = null)
    {
        parent::__construct($pName);
        
        $this->_helper = 'radio';
        $this->setAttribute('type', self::RADIO);
    }
    
    /**
     * @see MultipleAbstract::setAttribute()
     */
    public function setAttribute($pKey, $pValue) 
    {
        if($pKey == 'type')
        {
            $pValue = self::RADIO;
        }
        
        parent::setAttribute($pKey, $pValue);
    }

    /**
     * @see MultipleAbstract::removeAttribute()
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
     * @see MultipleAbstract::setAttributes()
     */
    public function setAttributes(array $pData)
    {
        $pData['type'] = self::RADIO;
        parent::setAttributes($pData);
    }
}
