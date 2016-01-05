<?php

namespace Elixir\I18N;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Plural
{
    /**
     * @var array
     */
    protected $_rules = [];

    public function __construct() 
    {
        $this->_rules['*'] = function(array $pMessages, $pNumber)
        {
            if($pNumber <= 0)
            {
                $result = $pMessages[0];
            }
            else if($pNumber == 1)
            {
                $result = count($pMessages) >= 3 ? $pMessages[1] : $pMessages[0];
            }
            else
            {
                $result = count($pMessages) >= 3 ? $pMessages[2] : $pMessages[1];
            }
            
            return str_replace('{COUNT}', $pNumber, $result);
        };
    }

    /**
     * @param string $pLocale
     * @return boolean
     */
    public function hasRule($pLocale)
    {
        return isset($this->_rules[$pLocale]);
    }
    
    /**
     * @param string $pLocale
     * @param callable $pRule
     */
    public function addRule($pLocale, callable $pRule)
    {
        $this->_rules[$pLocale] = $pRule;
    }
    
    /**
     * @param string $pLocale
     * @throws \LogicException
     */
    public function removeRule($pLocale)
    {
        if($pLocale === '*')
        {
            throw new \LogicException('You can not delete the default behavior.');
        }
        
        unset($this->_rules[$pLocale]);
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->_rules;
    }
    
    /**
     * @param array $pData
     */
    public function setRules(array $pData)
    {
        foreach($this->_rules as $locale => $rule)
        {
            if($locale === '*')
            {
                continue;
            }
            
            unset($this->_rules[$locale]);
        }
        
        foreach($pData as $locale => $rule)
        {
            $this->addRule($locale, $rule);
        }
    }
    
    /**
     * @param string|array $pMessage
     * @param float $pCount
     * @param string $pLocale
     * @return string
     */
    public function pluralize($pMessage, $pCount, $pLocale)
    {
        if(!is_array($pMessage))
        {
            $messages = [];
            
            if(preg_match_all('/\[([^\]\|]+\|[^\]]+)\]/', $pMessage, $matches, PREG_SET_ORDER))
            {
                foreach($matches as $result)
                {
                    $messages[] = explode('|', $result[1]);
                }
            }
        }
        else
        {
            $messages = [$pMessage];
        }
        
        foreach([$pLocale, '*'] as $locale)
        {
            if($this->hasRule($locale))
            {
                $rule = $this->_rules[$locale];
                
                foreach($messages as &$m)
                {
                    $m = $rule($m, $pCount);
                }
                
                break;
            }
        }
        
        if(isset($matches))
        {
            $message = $pMessage;
            
            foreach($matches as $key => $value)
            {
                $message = str_replace($value[0], $messages[$key], $message);
            }
        }
        else
        {
            $message = $messages[0];
        }
        
        return $message;
    }
}
