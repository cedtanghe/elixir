<?php

namespace Elixir\MVC\Exception;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
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
