<?php

namespace Elixir\Module\Facade\Macro;

use Elixir\Facade\Filter as Facade;
use Elixir\Filter\Escaper;
use Elixir\Filter\NbrFormat;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Filter
{
    public static function register() 
    {
        /************ BOOLEAN ************/
        
        Facade::macro(
            'boolean', 
            function($pContent, array $pOptions = array())
            {
                return Facade::filter('\Elixir\Filter\Boolean', $pContent, $pOptions);
            }
        );
        
        /************ CALLBACK ************/
        
        Facade::macro(
            'callback', 
            function($pContent, $pOptions = array())
            {
                if($pOptions instanceof \Closure)
                {
                    $pOptions = array('options' => $pOptions);
                }
            
                return Facade::filter('\Elixir\Filter\Callback', $pContent, $pOptions);
            }
        );
        
        /************ CHAIN ************/
        
        Facade::macro(
            'chain', 
            function($pContent, array $pFilters)
            {
                $chain = Facade::resolveInstance('\Elixir\Filter\Chain');
                $chain->setFilters($pFilters);
                
                return $chain->filter();
            }
        );
        
        /************ CROP ************/
        
        Facade::macro(
            'crop', 
            function($pContent, array $pOptions = array())
            {
                return Facade::filter('\Elixir\Filter\Crop', $pContent, $pOptions);
            }
        );
        
        /************ DATE ************/
        
        Facade::macro(
            'date', 
            function($pContent, array $pOptions = array())
            {
                return Facade::filter('\Elixir\Filter\Date', $pContent, $pOptions);
            }
        );
        
        /************ DUPLICATE ************/
        
        Facade::macro(
            'duplicate', 
            function($pContent, array $pOptions = array())
            {
                return Facade::filter('\Elixir\Filter\Duplicate', $pContent, $pOptions);
            }
        );
        
        /************ EMAIL ************/
        
        Facade::macro(
            'email', 
            function($pContent, array $pOptions = array())
            {
                return Facade::filter('\Elixir\Filter\Email', $pContent, $pOptions);
            }
        );
        
        /************ ENLARGE ************/
        
        Facade::macro(
            'enlarge', 
            function($pContent, array $pOptions = array())
            {
                return Facade::filter('\Elixir\Filter\Enlarge', $pContent, $pOptions);
            }
        );
        
        /************ ESCAPER ************/
        
        Facade::macro(
            'escape', 
            function($pContent, array $pOptions = array())
            {
                return Facade::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        Facade::macro(
            'escapeHTML', 
            function($pContent, array $pOptions = array())
            {
                $pOptions = array_merge(array('strategy' => Escaper::HTML), $pOptions);
                return Facade::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        Facade::macro(
            'escapeXML', 
            function($pContent, array $pOptions = array())
            {
                $pOptions = array_merge(array('strategy' => Escaper::XML), $pOptions);
                return Facade::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        Facade::macro(
            'escapeHTMLAttr', 
            function($pContent, array $pOptions = array())
            {
                $pOptions = array_merge(array('strategy' => Escaper::HTML_ATTR), $pOptions);
                return Facade::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        Facade::macro(
            'escapeXMLAttr', 
            function($pContent, array $pOptions = array())
            {
                $pOptions = array_merge(array('strategy' => Escaper::XML_ATTR), $pOptions);
                return Facade::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        Facade::macro(
            'escapeJS', 
            function($pContent, array $pOptions = array())
            {
                $pOptions = array_merge(array('strategy' => Escaper::JS), $pOptions);
                return Facade::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        Facade::macro(
            'escapeCSS', 
            function($pContent, array $pOptions = array())
            {
                $pOptions = array_merge(array('strategy' => Escaper::CSS), $pOptions);
                return Facade::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        Facade::macro(
            'escapeURL', 
            function($pContent, array $pOptions = array())
            {
                $pOptions = array_merge(array('strategy' => Escaper::URL), $pOptions);
                return Facade::filter('\Elixir\Filter\Escaper', $pContent, $pOptions);
            }
        );
        
        /************ FLOAT ************/
        
        Facade::macro(
            'float', 
            function($pContent, array $pOptions = array())
            {
                return Facade::filter('\Elixir\Filter\Float', $pContent, $pOptions);
            }
        );
        
        /************ INTEGER ************/
        
        Facade::macro(
            'int', 
            function($pContent)
            {
                return Facade::filter('\Elixir\Filter\Int', $pContent);
            }
        );
        
        /************ NUMBER FORMAT ************/
        
        Facade::macro(
            'nbrFormat', 
            function($pContent, array $pOptions = array())
            {
                return Facade::filter('\Elixir\Filter\NbrFormat', $pContent, $pOptions);
            }
        );
        
        Facade::macro(
            'formatNumber', 
            function($pContent, array $pOptions = array())
            {
                $pOptions = array_merge(array('strategy' => NbrFormat::FORMAT), $pOptions);
                return Facade::filter('\Elixir\Filter\NbrFormat', $pContent, $pOptions);
            }
        );
        
        Facade::macro(
            'formatCurrency', 
            function($pContent, array $pOptions = array())
            {
                $pOptions = array_merge(array('strategy' => NbrFormat::FORMAT_CURRENCY), $pOptions);
                return Facade::filter('\Elixir\Filter\NbrFormat', $pContent, $pOptions);
            }
        );
        
        /************ PROTECT ************/
        
        Facade::macro(
            'protect', 
            function($pContent)
            {
                return Facade::filter('\Elixir\Filter\Protect', $pContent);
            }
        );
        
        /************ RENAME ************/
        
        Facade::macro(
            'rename', 
            function($pContent, array $pOptions = array())
            {
                return Facade::filter('\Elixir\Filter\Rename', $pContent, $pOptions);
            }
        );
        
        /************ REPLACE ************/
        
        Facade::macro(
            'replace', 
            function($pContent, array $pOptions = array())
            {
                return Facade::filter('\Elixir\Filter\Replace', $pContent, $pOptions);
            }
        );
        
        /************ RESIZE ************/
        
        Facade::macro(
            'resize', 
            function($pContent, array $pOptions = array())
            {
                return Facade::filter('\Elixir\Filter\Resize', $pContent, $pOptions);
            }
        );
        
        /************ TRIM ************/
        
        Facade::macro(
            'trim', 
            function($pContent, array $pOptions = array())
            {
                return Facade::filter('\Elixir\Filter\Trim', $pContent, $pOptions);
            }
        );
    }
}