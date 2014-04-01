<?php

namespace Elixir\Facade;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Config extends FacadeAbstract
{
    /**
     * @see FacadeAbstract::getFacadeAccessor()
     */
    protected static function getFacadeAccessor()
    {
        return 'config';
    }
}