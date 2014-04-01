<?php

namespace Elixir\MVC\Exception;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class ForbiddenException extends \Exception
{
    /**
     * @param type $pMessage
     */
    public function __construct($pMessage = 'Forbidden') 
    {
        parent::__construct($pMessage, 500);
    }
}
