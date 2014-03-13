<?php

namespace Elixir\Form\Field;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Input extends FieldAbstract
{
    /**
     * @var string
     */
    const BUTTON = 'button';
    
    /**
     * @var string
     */
    const CHECKBOX = 'checkbox';
    
    /**
     * @var string
     */
    const FILE = 'file';
    
    /**
     * @var string
     */
    const HIDDEN = 'hidden';
    
    /**
     * @var string
     */
    const IMAGE = 'image';
    
    /**
     * @var string
     */
    const PASSWORD = 'password';
    
    /**
     * @var string
     */
    const RADIO = 'radio';
    
    /**
     * @var string
     */
    const RESET = 'reset';
    
    /**
     * @var string
     */
    const SUBMIT = 'submit';
    
    /**
     * @var string
     */
    const TEXT = 'text';
    
    /**
     * @var string
     */
    const COLOR = 'color';
    
    /**
     * @var string
     */
    const DATE = 'date';
    
    /**
     * @var string
     */
    const DATETIME = 'datetime';
    
    /**
     * @var string
     */
    const DATETIME_LOCAL = 'datetime-local';
    
    /**
     * @var string
     */
    const EMAIL = 'email';
    
    /**
     * @var string
     */
    const MONTH = 'month';
    
    /**
     * @var string
     */
    const NUMBER = 'number';
    
    /**
     * @var string
     */
    const RANGE = 'range';
    
    /**
     * @var string
     */
    const SEARCH = 'search';
    
    /**
     * @var string
     */
    const TEL = 'tel';
    
    /**
     * @var string
     */
    const TIME = 'time';
    
    /**
     * @var string
     */
    const URL = 'url';
    
    /**
     * @var string
     */
    const WEEK = 'week';
    
    /**
     * @var array
     */
    protected static $_excludes = array(
        self::FILE => '\Elixir\Form\Field\File',
        self::CHECKBOX => '\Elixir\Form\Field\Checkbox',
        self::RADIO => '\Elixir\Form\Field\Radio'
    );

    /**
     * @see FieldAbstract::__construct()
     */
    public function __construct($pName = null)
    {
        parent::__construct($pName);
        $this->_helper = 'input';
    }
    
    /**
     * @see FieldAbstract::setAttribute()
     * @throws \LogicException
     */
    public function setAttribute($pKey, $pValue) 
    {
        if($pKey == 'type')
        {
            if(array_key_exists($pValue, static::$_excludes))
            {
                throw new \LogicException(sprintf('The class "%s" class is better predisposed to such use.',
                                                  static::$_excludes[$pValue]));
            }
        }
        
        parent::setAttribute($pKey, $pValue);
    }
}