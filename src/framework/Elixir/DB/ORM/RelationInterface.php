<?php

namespace Elixir\DB\ORM;

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
     * @param mixed $value
     * @param boolean $filled
     */
    public function setRelated($value, $filled = true);

    /**
     * @return mixed
     */
    public function getRelated();

    /**
     * @param boolean $value
     */
    public function setFilled($value);

    /**
     * @return boolean
     */
    public function isFilled();

    public function load();
}
