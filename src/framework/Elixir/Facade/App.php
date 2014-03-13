<?php

namespace Elixir\Facade;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class App extends FacadeAbstract
{
    /**
     * @see FacadeAbstract::getFacadeAccessor()
     */
    protected static function getFacadeAccessor()
    {
        return 'application';
    }
}