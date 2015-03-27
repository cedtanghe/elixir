<?php

namespace Elixir\DB\ObjectMapper;

use Elixir\DB\ObjectMapper\SQL\Relation\Pivot;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface RelationInterfaceMetas extends RelationInterface 
{
    /**
     * @return string
     */
    public function getForeignKey();
    
    /**
     * @return string
     */
    public function getLocalKey();
    
    /**
     * @return Pivot
     */
    public function getPivot();
    
    /**
     * @return array
     */
    public function getCriterias();
}
