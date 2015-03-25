<?php

namespace Elixir\DB\ObjectMapper\SQL\Relation;

use Elixir\DB\ObjectMapper\Collection;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Relation implements RelationInterface 
{
    /**
     * @var mixed
     */
    protected $related;

    /**
     * @var boolean
     */
    protected $filled = false;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @see RelationInterface::getType()
     */
    public function getType()
    {
        return self::CUSTOM;
    }

    /**
     * @see RelationInterface::setRelated()
     */
    public function setRelated($value, $filled = true)
    {
        if (is_array($value))
        {
            $value = new Collection($value, false);
        }

        $this->related = $value;
        $this->filled = $filled;
    }
    
    /**
     * @see RelationInterface::getRelated()
     */
    public function getRelated() 
    {
        return $this->related;
    }

    /**
     * @see RelationInterface::isFilled()
     */
    public function isFilled()
    {
        return $this->filled;
    }

    /**
     * @see RelationInterface::isFilled()
     */
    public function load() 
    {
        $this->setRelated(call_user_func($this->callback), true);
    }
}
