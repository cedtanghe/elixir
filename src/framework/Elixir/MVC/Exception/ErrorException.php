<?php

namespace Elixir\MVC\Exception;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class ErrorException extends \Exception
{
    /**
     * @param type $pMessage
     */
    public function __construct($pMessage = 'Internal Server Error') 
    {
        parent::__construct($pMessage, 500);
    }
}
