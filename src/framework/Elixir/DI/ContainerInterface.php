<?php

namespace Elixir\DI;

use Elixir\DI\ProviderInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface ContainerInterface 
{
    /**
     * @param ProviderInterface $provider
     */
    public function addProvider(ProviderInterface $provider);
    
    /**
     * @param string $key
     * @return boolean 
     */
    public function has($key);

    /**
     * @param string $key
     * @param array $options
     * @param mixed $default
     * @return mixed 
     */
    public function get($key, array $options = [], $default = null);
    
    /**
     * @param callable|string $callback
     * @param array $available
     * @return array
     */
    public function resolve($callback, array $available = []);

    /**
     * @param string $key
     * @param mixed $value 
     * @param array $options 
     */
    public function bind($key, $value, array $options = []);
    
    /**
     * @param string $key
     * @param mixed $value 
     * @param array $options 
     */
    public function share($key, $value, array $options = []);
    
    /**
     * @param string $key 
     */
    public function unbind($key);

    /**
     * @param array $options
     * @return array 
     */
    public function all(array $options = []);

    /**
     * @param array $data
     */
    public function replace(array $data);

    /**
     * @param string $key
     * @param callable $value
     */
    public function extend($key, callable $value);
    
    /**
     * @param string $key
     * @param string $alias
     */
    public function addAlias($key, $alias);

    /**
     * @param string $key
     * @param string $tag
     */
    public function addTag($key, $tag);

    /**
     * @param string $tag
     * @return array
     */
    public function findByTag($tag);

    /**
     * @param string $key
     * @return array 
     */
    public function raw($key);
    
    /**
     * @param array|ContainerInterface $data 
     */
    public function merge($data);
}
