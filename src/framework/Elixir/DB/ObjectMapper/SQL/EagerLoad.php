<?php

namespace Elixir\DB\ObjectMapper\SQL;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class EagerLoad 
{
    public function __construct($target, array $config = []) 
    {
        $config = array_merge(
            [
                'foreign_key' => null,
                'other_key' => null,
                'pivot' => null,
            ],
            $config
        );
    }
    
    public function sync($member, array $repositories, array $with = [])
    {
        // Todo
    }
}
