<?php

namespace Elixir\Helper;

use Elixir\Helper\HelperInterface;
use Elixir\I18N\I18NInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
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
     * @param array $pOptions
     * @param string $pTextDomain
     * @return string
     */
    public function translate($pMessage, array $pOptions = array())
    {
        $pOptions = array_merge(
            array(
                'locale' => null, 
                'textDomain' => I18NInterface::ALL_TEXT_DOMAINS
            ),
            $pOptions
        );
        
        $result = $this->_I18N->translate($pMessage, $pOptions['locale'], $pOptions['textDomain']);
        
        if(isset($pOptions['replace']))
        {
            $result = str_replace(array_keys($pOptions['replace']), array_values($pOptions['replace']), $result);
        }
        
        return $result;
    }
    
    /**
     * @see I18N::translate()
     */
    public function _($pMessage, array $pOptions = array())
    {
        return $this->translate($pMessage, $pOptions);
    }

    /**
     * @param string|array $pMessage
     * @param float $pCount
     * @param array $pOptions
     * @return string
     */
    public function pluralize(array $pMessage, $pCount, array $pOptions = array())
    {
        $pOptions = array_merge(array('locale' => null), $pOptions);
        $result = $this->_I18N->plurialize($pMessage, $pCount, $pOptions['locale']);
        
        if(isset($pOptions['replace']))
        {
            $result = str_replace(array_keys($pOptions['replace']), array_values($pOptions['replace']), $result);
        }
        
        return $result;
    }
    
    /**
     * @param string|array $pMessage
     * @param float $pCount
     * @param array $pOptions
     * @return string
     */
    public function transPlural($pMessage, $pCount, array $pOptions = array())
    {
        $pOptions = array_merge(
            array(
                'locale' => null, 
                'textDomain' => I18NInterface::ALL_TEXT_DOMAINS
            ),
            $pOptions
        );
        
        $result = $this->_I18N->transPlural($pMessage, $pOptions['locale'], $pOptions['textDomain']);
        
        if(isset($pOptions['replace']))
        {
            $result = str_replace(array_keys($pOptions['replace']), array_values($pOptions['replace']), $result);
        }
        
        return $result;
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