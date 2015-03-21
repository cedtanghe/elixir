<?php

namespace Elixir\DB\ObjectMapper;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface FindableInterface
{
    /**
     * @param callable $extension
     */
    public function extend(callable $extension);

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
