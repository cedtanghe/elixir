<?php

namespace Elixir\HTTP;

use Elixir\HTTP\Session\Session;
use Elixir\MVC\Application;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class RequestFactory
{
    /**
     * @param Sanitizer $pSanitizer
     * @return Request
     */
    public static function create(Sanitizer $pSanitizer = null)
    {
        $sanitizer = $pSanitizer;
        
        if(null === $sanitizer)
        {
            $sanitizer = new Sanitizer();
            
            if(class_exists('\Elixir\MVC\Application') && null !== Application::$registry)
            {
                $sanitizer->setContainer(Application::$registry);
            }
        }
        
        $param = function($pData, $pAutoSanitization = true) use($sanitizer)
        {
            $parameters = new Parameters($pData);
            $parameters->setAutoSanitization($pAutoSanitization);
            $parameters->setSanitizer($sanitizer);
            
            return $parameters;
        };
        
        $request = new Request(
            $param(array(), true),
            $param($_GET, true),
            $param($_POST, true),
            new SessionParameters(Session::instance() ?: new Session()),
            $param($_COOKIE, false),
            new FileParameters($_FILES),
            $param($_SERVER, false),
            $param($_ENV, false)
        );
        
        $request->setHeaders(Headers::fromApacheRequestHeaders($request->getServer()->gets()));
        return $request;
    }
}
