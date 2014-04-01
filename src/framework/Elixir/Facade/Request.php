<?php

namespace Elixir\Facade;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Request extends FacadeAbstract
{
    /**
     * @see FacadeAbstract::getFacadeAccessor()
     */
    protected static function getFacadeAccessor()
    {
        return 'request';
    }
}