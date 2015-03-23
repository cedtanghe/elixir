<?php

namespace Elixir\DB\ObjectMapper;

use Elixir\DB\ObjectMapper\FindableExtensionInterface;
use Elixir\DB\ORM\RepositoryInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface FindableInterface
{
    /**
     * @param FindableExtensionInterface $extension
     */
    public function extend(FindableExtensionInterface $extension);

    /**
     * @return boolean
     */
    public function has();
    
    /**
     * @return integer
     */
    public function count();
    
    /**
     * @return array
     */
    public function raw();
    
    /**
     * @return RepositoryInterface|null
     */
    public function one();
    
    /**
     * @return Collection
     */
    public function all();
}
