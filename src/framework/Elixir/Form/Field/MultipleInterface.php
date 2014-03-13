<?php

namespace Elixir\Form\Field;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface MultipleInterface
{
    /**
     * @var boolean
     */
    const DATA_USE_KEYS = true;
    
    /**
     * @var boolean
     */
    const DATA_USE_VALUES = false;
    
    /**
     * @param array $pValue
     * @param boolean $pType
     */
    public function setData(array $pValue, $pType = self::DATA_USE_VALUES);
    
    /**
     * @return array
     */
    public function getData();
}