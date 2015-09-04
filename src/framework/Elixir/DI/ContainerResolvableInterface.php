<?php

namespace Elixir\DI;

use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface ContainerResolvableInterface extends ContainerInterface 
{
    /**
     * @param callable $converter
     */
    public function addConverter(callable $converter);
    
    /**
     * @param string $id
     * @return string
     */
    public function convert($id);
    
    /**
     * @param string $when
     * @param string $needs
     * @param mixed $implementation
     */
    public function addContextualBinding($when, $needs, $implementation);
   
    /**
     * @param callable|string $callback
     * @param array $options
     * @return array
     */
    public function resolve($callback, array $options = []);
}
