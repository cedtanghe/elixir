<?php

namespace Elixir\I18N;

use Elixir\Config\I18N\WriterInterface;
use Elixir\Dispatcher\Dispatcher;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class I18N extends Dispatcher implements I18NInterface
{
    /**
     * @var string
     */
    protected $_locale;
    
    /**
     * @var array
     */
    protected $_textDomains = array();
    
    /**
     * @var Plural 
     */
    protected $_plural;
    
    /**
     * @param array $pTextDomains
     * @param string $pLocale
     */
    public function __construct($pTextDomains = array(), $pLocale = null) 
    {
        $this->setTextDomains($pTextDomains);
        
        if(null !== $pLocale)
        {
            $this->setLocale($pLocale);
        }
    }
    
    /**
     * @see I18NInterface::setLocale()
     */
    public function setLocale($pValue)
    {
        $this->_locale = $pValue;
    }
    
    /**
     * @see I18NInterface::setLocale()
     */
    public function getLocale()
    {
        if(null === $this->_locale)
        {
            $this->_locale = Locale::getDefault();
        }
        
        return $this->_locale;
    }
    
    /**
     * @param Plural $pValue
     */
    public function setPlural(Plural $pValue)
    {
        $this->_plural = $pValue;
    }
    
    /**
     * @return Plural
     */
    public function getPlural()
    {
        if(null === $this->_plural)
        {
            $this->_plural = new Plural();
        }
        
        return $this->_plural;
    }
    
    /**
     * @param mixed $pResources
     * @param string $pLocale
     */
    public function load($pResource, $pLocale = null)
    {
        $locale = $pLocale ?: $this->getLocale();
        
        if($pResource instanceof self)
        {
            $this->merge($pResource);
        }
        else
        {
            foreach((array)$pResource as $key => $value)
            {
                if(is_numeric($key))
                {
                    $key = self::DEFAULT_TEXT_DOMAIN;
                }
                
                if(!$this->hasTextDomain($key))
                {
                    $this->addTextDomain(new TextDomain(), $key);
                }
                
                $this->getTextDomain($key)->addResource($value, $locale);
            }
        }
    }
    
    /**
     * @param WriterInterface $pWriter
     * @param string $pFile
     * @return boolean
     */
    public function export(WriterInterface $pWriter, $pFile)
    {
        $pWriter->setI18N($this);
        return $pWriter->export($pFile);
    }
    
    /**
     * @param string $pName
     * @return boolean
     */
    public function hasTextDomain($pName)
    {
        return isset($this->_textDomains[$pName]);
    }
    
    /**
     * @param TextDomain $pTextDomain
     * @param string $pName
     */
    public function addTextDomain(TextDomain $pTextDomain, $pName = self::DEFAULT_TEXT_DOMAIN)
    {
        if($this->hasTextDomain($pName))
        {
            $this->getTextDomain($pName)->merge($pTextDomain);
        }
        else
        {
            $this->_textDomains[$pName] = $pTextDomain;
        }
    }
    
    /**
     * @param string $pName
     * @param mixed $pDefault
     * @return mixed
     */
    public function getTextDomain($pName, $pDefault = null)
    {
        if($this->hasTextDomain($pName))
        {
            return $this->_textDomains[$pName];
        }
        
        if(is_callable($pDefault))
        {
            return call_user_func($pDefault);
        }
        
        return $pDefault;
    }
    
    /**
     * @param string $pName
     */
    public function removeTextDomain($pName)
    {
        unset($this->_textDomains[$pName]);
    }
    
    /**
     * @return array
     */
    public function getTextDomains()
    {
        return $this->_textDomains;
    }
    
    /**
     * @param array $pData
     */
    public function setTextDomains(array $pData)
    {
        $this->_textDomains = array();
        
        foreach($pData as $name => $textDomain)
        {
            $this->addTextDomain($textDomain, $name);
        }
    }
    
    /**
     * @see I18NInterface::translate()
     */
    public function translate($pMessage, $pLocale = null, $pTextDomain = self::ALL_TEXT_DOMAINS)
    {
        $message = $pMessage;
        $textDomains = $pTextDomain == self::ALL_TEXT_DOMAINS ? array_keys($this->_textDomains) : array($pTextDomain); 
        $locale = $pLocale ?: $this->getLocale();
        $type = I18NEvent::MISSING_TRANSLATION;
        
        foreach($textDomains as $name)
        {
            if($this->getTextDomain($name)->has($message, $locale))
            {
                $type = I18NEvent::TRANSLATION_FOUND;
                $message = $this->getTextDomain($name)->get($message, $locale);

                break;
            }
        }
        
        $event = new I18NEvent($type, $message, $locale);
        $this->dispatch($event);
        
        return $event->getMessage();
    }
    
    /**
     * @see I18NInterface::pluralize()
     */
    public function pluralize($pMessage, $pCount, $pLocale = null)
    {
        return $this->getPlural()->pluralize($pMessage, $pCount, $pLocale ?: $this->getLocale());
    }
    
    /**
     * @see I18NInterface::transPlural()
     */
    public function transPlural($pMessage, $pCount, $pLocale = null, $pTextDomain = self::ALL_TEXT_DOMAINS)
    {
        return $this->pluralize($this->translate($pMessage, $pLocale, $pTextDomain), $pCount, $pLocale);
    }
    
    /**
     * @param array|I18NInterface $pData
     */
    public function merge($pData)
    {
        if($pData instanceof self)
        {
            $pData = $pData->getTextDomains();
        }
        
        foreach($pData as $name => $textDomain)
        {
            $this->addTextDomain($textDomain, $name);
        }
    }
}