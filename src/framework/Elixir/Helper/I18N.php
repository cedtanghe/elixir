<?php

namespace Elixir\Helper;

use Elixir\I18N\I18NInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class I18N implements HelperInterface
{
    /**
     * @var I18NInterface 
     */
    protected $_I18N;
    
    /**
     * @param I18NInterface $pI18N
     */
    public function __construct(I18NInterface $pI18N)
    {
        $this->_I18N = $pI18N;
    }
    
    /**
     * @return I18NInterface
     */
    public function getI18N()
    {
        return $this->_I18N;
    }

    /**
     * @param string|array $pMessage
     * @param string $pLocale
     * @param string $pTextDomain
     * @return string
     */
    public function translate($pMessage, $pLocale = null, $pTextDomain = I18NInterface::ALL_TEXT_DOMAINS)
    {
        return $this->_I18N->translate($pMessage, $pLocale, $pTextDomain);
    }
    
    /**
     * @see I18N::translate()
     */
    public function _($pMessage, $pLocale = null, $pTextDomain = I18NInterface::ALL_TEXT_DOMAINS)
    {
        return $this->translate($pMessage, $pLocale, $pTextDomain);
    }

    /**
     * @param string|array $pMessage
     * @param float $pCount
     * @param string $pLocale
     * @return string
     */
    public function pluralize(array $pMessage, $pCount, $pLocale = null)
    {
        return $this->_I18N->plurialize($pMessage, $pCount, $pLocale);
    }
    
    /**
     * @param string|array $pMessage
     * @param float $pCount
     * @param string $pLocale
     * @param string $pTextDomain
     * @return string
     */
    public function transPlural($pMessage, $pCount, $pLocale = null, $pTextDomain = I18NInterface::ALL_TEXT_DOMAINS)
    {
        return $this->_I18N->transPlural($pText, $pCount, $pLocale, $pTextDomain);
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     * @return string
     * @throws \BadMethodCallException
     */
    public function __call($pMethod, $pArguments) 
    {
        if(substr($pMethod, 0, 11) == 'transPlural')
        {
            $pMethod = 'transPlural';
        }
        else if(substr($pMethod, 0, 5) == 'trans' || substr($pMethod, 0, 1) == '_')
        {
            $pMethod = 'translate';
        }
        else
        {
            throw new \BadMethodCallException(sprintf('Method "%s" does not exist.', $pMethod));
        }
        
        return call_user_func_array(array($this, $pMethod), $pArguments);
    }

    /**
     * @see I18N::translate()
     */
    public function direct()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'translate'), $args);
    }
}