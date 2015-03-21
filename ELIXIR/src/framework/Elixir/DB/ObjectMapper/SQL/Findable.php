<?php

namespace Elixir\DB\ObjectMapper\SQL;

use Elixir\DB\ObjectMapper\FindableInterface;
use Elixir\DB\ObjectMapper\RepositoryInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Findable implements FindableInterface 
{
    public function __construct(RepositoryInterface $repository)
    {
        // Todo
    }
    
    /**
     * @see FindableInterface::extend()
     */
    public function extend(callable $extension)
    {
        // Todo
    }

    /**
     * @param string $part
     * @return Findable
     */
    public function reset($part)
    {
        // Todo
        return $this;
    }

    /**
     * @see FindableInterface::has()
     */
    public function has() 
    {
        return $this->count() > 0;
    }

    /**
     * @see FindableInterface::count()
     */
    public function count()
    {
        // Todo
    }

    /**
     * @see FindableInterface::raw()
     */
    public function raw() 
    {
        // Todo
    }

    /**
     * @see FindableInterface::one()
     */
    public function one() 
    {
        // Todo
    }

    /**
     * @see FindableInterface::all()
     */
    public function all()
    {
        // Todo
    }
}
