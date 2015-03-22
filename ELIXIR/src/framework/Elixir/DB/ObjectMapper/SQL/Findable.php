<?php

namespace Elixir\DB\ObjectMapper\SQL;

use Elixir\DB\ObjectMapper\FindableExtensionInterface;
use Elixir\DB\ObjectMapper\FindableInterface;
use Elixir\DB\ObjectMapper\RepositoryInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Findable implements FindableInterface 
{
    /**
     * @param RepositoryInterface $repository
     * @param mixed $options
     */
    public function __construct(RepositoryInterface $repository, $options = null)
    {
        // Todo
    }
    
    /**
     * @see FindableInterface::extend()
     */
    public function extend(FindableExtensionInterface $extension)
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
