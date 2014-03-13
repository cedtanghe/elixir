<?php

namespace Elixir\I18N;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class I18NEvent extends Event
{
    /**
     * @var string
     */
    const TRANSLATION_FOUND = 'translation_found';
    
    /**
     * @var string
     */
    const MISSING_TRANSLATION = 'missing_translation';
    
    /**
     * @var string
     */
    protected $_message;
    
    /**
     * @var string
     */
    protected $_locale;

    /**
     * @see Event::__contruct()
     * @param string $pMessage
     * @param string $pLocale
     */
    public function __construct($pType, $pMessage = null, $pLocale = null) 
    {
        $this->_type = $pType;
        $this->_message = $pMessage;
        $this->_locale = $pLocale;
    }
    
    /**
     * @param string $pValue
     */
    public function setMessage($pValue)
    {
        $this->_message = $pValue;
    }
    
    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
    
    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->_locale;
    }
}
