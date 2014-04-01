<?php

namespace Elixir\DB\ORM\Relation;

use Elixir\DB\ORM\Collection;
use Elixir\DB\ORM\RepositoryInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface RelationInterface
{
    /**
     * @var string
     */
    const HAS_ONE = 'has_one';
    
    /**
     * @var string
     */
    const HAS_MANY = 'has_many';
    
    /**
     * @var string
     */
    const BELONGS_TO = 'belongs_to';
    
    /**
     * @var string
     */
    const CUSTOM = 'custom';
    
    /**
     * @return string
     */
    public function getType();
    
    /**
     * @param RepositoryInterface|Collection|null $pValue
     * @param boolean $pFilled
     */
    public function setRelated($pValue, $pFilled = true);
    
    /**
     * @return RepositoryInterface|Collection|null
     */
    public function getRelated();
    
    /**
     * @param boolean $pValue
     */
    public function setFilled($pValue);
    
    /**
     * @return boolean
     */
    public function isFilled();
    
    public function load();
}