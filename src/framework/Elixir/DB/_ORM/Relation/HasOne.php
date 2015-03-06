<?php

namespace Elixir\DB\ORM\Relation;

use Elixir\DB\ORM\Relation\HasOneOrMany;
use Elixir\DB\ORM\Relation\Pivot;
use Elixir\DB\ORM\RepositoryInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class HasOne extends HasOneOrMany
{
    /**
     * @see HasOneOrMany::__construct()
     */
    public function __construct(RepositoryInterface $pRepository, 
                                $pTarget, 
                                $pForeignKey, 
                                $pOtherKey = null, 
                                Pivot $pPivot = null)
    {
        parent::__construct(
            $pRepository, 
            $pTarget, 
            $pForeignKey, 
            $pOtherKey,
            $pPivot,
            self::HAS_ONE
        );
    }
}
