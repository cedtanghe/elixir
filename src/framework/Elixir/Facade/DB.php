<?php

namespace Elixir\Facade;

use Elixir\Facade\FacadeAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class DB extends FacadeAbstract
{
    /**
     * @param string $pAccessor
     * @return mixed
     */
    public static function with($pAccessor)
    {
        return static::resolveInstance($pAccessor);
    }

    /**
     * @see FacadeAbstract::getFacadeAccessor()
     */
    protected static function getFacadeAccessor()
    {
        return 'db.default';
    }
}