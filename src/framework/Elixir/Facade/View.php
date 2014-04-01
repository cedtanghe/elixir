<?php

namespace Elixir\Facade;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class View extends FacadeAbstract
{
    /**
     * @see FacadeAbstract::getFacadeAccessor()
     */
    protected static function getFacadeAccessor()
    {
        return 'view';
    }
}