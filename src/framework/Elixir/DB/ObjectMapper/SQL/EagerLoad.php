<?php

namespace Elixir\DB\ObjectMapper\SQL;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class EagerLoad 
{
    public function __construct($target, 
                                $foreignKey, 
                                $otherKey = null,
                                Pivot $pivot = null) 
    {
        // Todo
    }
    
    public function sync($member, array $repositories, array $with = [])
    {
        // Todo
    }
}
