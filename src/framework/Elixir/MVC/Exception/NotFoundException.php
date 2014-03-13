<?php

namespace Elixir\MVC\Exception;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class NotFoundException extends \Exception
{
    /**
     * @param type $pMessage
     */
    public function __construct($pMessage = 'Not Found') 
    {
        parent::__construct($pMessage, 404);
    }
}
