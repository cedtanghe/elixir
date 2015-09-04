<?php

namespace Elixir\Util;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
trait MacroableTrait 
{
    /**
     * @var array 
     */
    protected static $macros = [];

    /**
     * @param string $name
     * @param callable $macro
     */
    public static function macro($name, callable $macro)
    {
        static::$macros[$name] = $macro;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public static function hasMacro($name) 
    {
        return isset(static::$macros[$name]);
    }

    /**
     * @ignore
     * @throws \BadMethodCallException
     */
    public static function __callStatic($name, $arguments)
    {
        if (static::hasMacro($name)) 
        {
            return call_user_func_array(static::$macros[$name], $arguments);
        }

        throw new \BadMethodCallException(sprintf('Method "%s" is not available.', $name));
    }
}
