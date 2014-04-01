<?php

namespace Elixir\DB\ORM\Relation;

use Elixir\DB\ORM\Collection;
use Elixir\DB\ORM\RepositoryInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Relation implements RelationInterface
{
    /**
     * @var RepositoryInterface|Collection
     */
    protected $_related;
    
    /**
     * @var boolean
     */
    protected $_filled = false;
    
    /**
     * @var \Closure
     */
    protected $_callback;
    
    /**
     * @param \Closure $pCallable
     */
    public function __construct(\Closure $pCallback)
    {
        $this->_callback = $pCallback;
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
    public function setRelated($pValue, $pFilled = true)
    {
        if(is_array($pValue))
        {
            $pValue = new Collection($pValue, true);
        }
        
        $this->_related = $pValue;
        $this->_filled = $pFilled;
    }
    
    /**
     * @see RelationInterface::getRelated()
     */
    public function getRelated()
    {
        return $this->_related;
    }
    
    /**
     * @see RelationInterface::setFilled()
     */
    public function setFilled($pValue)
    {
        $this->_filled = $pValue;
    }
    
    /**
     * @see RelationInterface::isFilled()
     */
    public function isFilled()
    {
        return $this->_filled;
    }
    
    /**
     * @see RelationInterface::isFilled()
     */
    public function load()
    {
        $callback = $this->_callback;
        $this->setRelated($callback(), true);
    }
}