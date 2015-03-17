<?php

namespace Elixir\Filter;

use Elixir\Filter\FilterAbstract;
use Elixir\Filter\FilterInterface;
use Elixir\I18N\Locale;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Date extends FilterAbstract
{
    /**
     * @see FilterInterface::filter()
     */
    public function filter($pContent, array $pOptions = [])
    {
        $pOptions = array_merge($this->_options, $pOptions);
        
        if($pContent instanceof \DateTime)
        {
            $date = $pContent;
        }
        else
        {
            $input = isset($pOptions['input']) ? $pOptions['input'] : 'd/m/Y';
            $date = \DateTime::createFromFormat($input, $pContent);
            
            if(!$date)
            {
                return '';
            }
        }
        
        if(extension_loaded('intl') && isset($pOptions['ICU']) && true === $pOptions['ICU'])
        {
            $output = isset($pOptions['output']) ? $pOptions['output'] : 'yyyy-MM-dd\'T\'HH:mm:ssZZZZZ';
            $locale = isset($pOptions['locale']) ? $pOptions['locale'] : Locale::getDefault();
            $timezone = isset($pOptions['timezone']) ? $pOptions['timezone'] : \date_default_timezone_get();
            
            $dateFormatter = \IntlDateFormatter::create($locale,
                                                        \IntlDateFormatter::NONE,
                                                        \IntlDateFormatter::NONE,
                                                        $timezone,
                                                        \IntlDateFormatter::GREGORIAN,
                                                        $output);

            return $dateFormatter->format($date);
        }
        else
        {
            $output = isset($pOptions['output']) ? $pOptions['output'] : 'Y-m-d H:i:s';
            return $date->format($output);
        }
    }
}
