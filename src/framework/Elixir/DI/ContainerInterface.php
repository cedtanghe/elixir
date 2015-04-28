<?php

namespace Elixir\DI;

use Elixir\DI\ContainerInterface;
use Elixir\DI\ProviderInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface ContainerInterface 
{
    /**
     * @var string
     */
    const BIND = 'bind';

    /**
     * @var string
     */
    const SINGLETON = 'singleton';

    /**
     * @var string
     */
    const WRAP = 'wrap';

    /**
     * @var string
     */
    const EXTEND = 'extend';

    /**
     * @var string
     */
    const UNLOCKED = 'unlocked';

    /**
     * @var string
     */
    const READ_ONLY = 'read_only';

    /**
     * @var string
     */
    const IGNORE_IF_ALREADY_EXISTS = 'ignore_if_already_exists';

    /**
     * @var string
     */
    const THROW_IF_ALREADY_EXISTS = 'throw_if_already_exists';

    /**
     * @param string $value
     */
    public function setLockMode($value);

    /**
     * @return string
     */
    public function getLockMode();

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
     * @param string $key
     * @param mixed $value 
     * @param array $options 
     */
    public function set($key, $value, array $options = []);

    /**
     * @param string $key 
     */
    public function remove($key);

    /**
     * @param array $options
     * @return array 
     */
    public function gets(array $options = []);

    /**
     * @param array $data
     * @param array $options
     */
    public function sets(array $data, array $options = []);

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
     * @param array $arguments
     * @param mixed $default
     * @return array|mixed
     */
    public function findByTag($tag, array $arguments = [], $default = null);

    /**
     * @param string $key
     * @return array 
     */
    public function raw($key);

    /**
     * @param ProviderInterface $provider
     */
    public function addProvider(ProviderInterface $provider);

    /**
     * @param array|ContainerInterface $data 
     */
    public function merge($data);
}
