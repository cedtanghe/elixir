<?php

namespace Elixir\I18N;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface I18NInterface
{
    /**
     * @var string
     */
    const DEFAULT_TEXT_DOMAIN = 'default';
    
    /**
     * @var string
     */
    const ALL_TEXT_DOMAINS = '*';
    
    /**
     * @param string $pValue
     */
    public function setLocale($pValue);
    
    /**
     * @return string
     */
    public function getLocale();
    
    /**
     * @return array
     */
    public function getTextDomains();
    
    /**
     * @param string|array $pMessage
     * @param string $pLocale
     * @param string $pTextDomain
     * @return string
     */
    public function translate($pMessage, $pLocale = null, $pTextDomain = self::DEFAULT_TEXT_DOMAIN);
    
    /**
     * @param string|array $pMessage
     * @param float $pCount
     * @param string $pLocale
     * @return string
     */
    public function pluralize($pMessage, $pCount, $pLocale = null);
    
    /**
     * @param string|array $pMessage
     * @param float $pCount
     * @param string $pLocale
     * @param string $pTextDomain
     * @return string
     */
    public function transPlural($pMessage, $pCount, $pLocale = null, $pTextDomain = self::DEFAULT_TEXT_DOMAIN);
}