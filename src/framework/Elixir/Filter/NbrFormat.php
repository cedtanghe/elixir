<?php

namespace Elixir\Filter;

use Elixir\Filter\FilterAbstract;
use Elixir\Filter\FilterInterface;
use Elixir\I18N\Locale;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class NbrFormat extends FilterAbstract
{
    /**
     * @var string
     */
    const FORMAT = 'format';
    
    /**
     * @var string
     */
    const FORMAT_CURRENCY = 'format_currency';
    
    /**
     * @see FilterInterface::filter()
     */
    public function filter($pContent, array $pOptions = array())
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $filter = isset($pOptions['filter']) ? $pOptions['filter'] : self::FORMAT;
        
        switch($filter)
        {
            case self::FORMAT:
                return $this->format(
                    $pContent, 
                    isset($pOptions['type']) ? $pOptions['type'] : null,
                    isset($pOptions['locale']) ? $pOptions['locale'] : null
                );
            break;
            case self::FORMAT_CURRENCY:
                return $this->formatCurrency(
                    $pContent,
                    $pOptions['currency'],
                    isset($pOptions['locale']) ? $pOptions['locale'] : null
                );
            break;
        }
    }
    
    /**
     * @param float $pValue
     * @param integer $pType
     * @param string $pLocale
     * @return string
     * @throws \RuntimeException
     */
    public function format($pValue, $pType = null, $pLocale = null)
    {
        if(!extension_loaded('intl'))
        {
            throw new \RuntimeException('Class "\NumberFormatter" does not exist, please install the "intl" extension.');
        }
        
        $locale = $pLocale ?: Locale::getDefault();
        $type = $pType ?: \NumberFormatter::TYPE_DEFAULT;
        
        $fmt = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
        return $fmt->format($pValue, $type);
    }
    
    /**
     * @param float $pValue
     * @param string $pCurrency
     * @param string $pLocale
     * @return string
     * @throws \RuntimeException
     */
    public function formatCurrency($pValue, $pCurrency = 'EUR', $pLocale = null)
    {
        if(!extension_loaded('intl'))
        {
            throw new \RuntimeException('Class "\NumberFormatter" does not exist, please install the "intl" extension.');
        }
        
        $locale = $pLocale ?: Locale::getDefault();
        
        $fmt = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        return $fmt->formatCurrency($pValue, $pCurrency);
    }
}