<?php

namespace Elixir\Module\Facade\Macro;

use Elixir\Facade\Validator as Facade;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Validator
{
    public static function register() 
    {
        /************ BOOLEAN ************/
        
        Facade::macro(
            'boolean', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\Boolean', $pContent, $pOptions);
            }
        );
        
        /************ CSRF ************/
        
        Facade::macro(
            'csrf', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('validator.csrf', $pContent, $pOptions);
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
            
                return Facade::valid('\Elixir\Validator\Callback', $pContent, $pOptions);
            }
        );
        
        /************ CHAIN ************/
        
        Facade::macro(
            'chain', 
            function($pContent, array $pValidators, array $pOptions = array())
            {
                $chain = Facade::resolveInstance('\Elixir\Validator\Chain');
                $chain->setValidators($pValidators);
                
                return $chain->isValid($pContent, $pOptions);
            }
        );
        
        /************ DATE ************/
        
        Facade::macro(
            'date', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\Date', $pContent, $pOptions);
            }
        );
        
        /************ EMAIL ************/
        
        Facade::macro(
            'email', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\Email', $pContent, $pOptions);
            }
        );
        
        /************ EQUAL ************/
        
        Facade::macro(
            'equal', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\Equal', $pContent, $pOptions);
            }
        );
        
        /************ EXTENSION ************/
        
        Facade::macro(
            'extension', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\Extension', $pContent, $pOptions);
            }
        );
        
        /************ FILE SIZE ************/
        
        Facade::macro(
            'fileSize', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\FileSize', $pContent, $pOptions);
            }
        );
        
        /************ FLOAT ************/
        
        Facade::macro(
            'float', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\Float', $pContent, $pOptions);
            }
        );
        
        /************ FORMAT ************/
        
        Facade::macro(
            'format', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\Format', $pContent, $pOptions);
            }
        );
        
        /************ IP ************/
        
        Facade::macro(
            'ip', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\IP', $pContent, $pOptions);
            }
        );
        
        /************ INTEGER ************/
        
        Facade::macro(
            'int', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\Int', $pContent, $pOptions);
            }
        );
        
        /************ LENGTH ************/
        
        Facade::macro(
            'length', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\Length', $pContent, $pOptions);
            }
        );
        
        /************ MIME TYPE ************/
        
        Facade::macro(
            'mimeType', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\MimeType', $pContent, $pOptions);
            }
        );
        
        /************ NOT EMPTY ************/
        
        Facade::macro(
            'notEmpty', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\NotEmpty', $pContent, $pOptions);
            }
        );
        
        /************ RANGE ************/
        
        Facade::macro(
            'range', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\Range', $pContent, $pOptions);
            }
        );
        
        /************ REGEX ************/
        
        Facade::macro(
            'regex', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\Regex', $pContent, $pOptions);
            }
        );
        
        /************ URL ************/
        
        Facade::macro(
            'url', 
            function($pContent, array $pOptions = array())
            {
                return Facade::valid('\Elixir\Validator\URL', $pContent, $pOptions);
            }
        );
    }
}