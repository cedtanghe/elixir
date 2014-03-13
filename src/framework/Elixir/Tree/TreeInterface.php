<?php

namespace Elixir\Tree;

/**
 * @author Nicola Pertosa <n.pertosa@peoleo.fr>
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface TreeInterface 
{
    /**
     * @var integer 
     */
    const ALL_LEVEL = -1;
    
    /**
     * @return integer 
     */
    public function getLevel();
    
    /**
     * @internal
     * @param integer $pLevel
     */
    public function setLevel($pValue);
    
    /**
     * @return array
     */
    public function getParameters();
    
    /**
     * @param boolean $pWithInfos
     * @return array
     */
    public function getChildren($pWithInfos = false);
         
    public function sort();
    
    /**
     * @param array $pParameter
     * @param integer $pLevel
     * @param boolean $pAll
     * @return TreeInterface|array
     */
    public function find(array $pParameters = array(), $pLevel = self::ALL_LEVEL, $pAll = false);
}