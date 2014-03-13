<?php

namespace Elixir\DB;

use Elixir\DB\Result\SetAbstract;
use Elixir\DB\SQL\SQLAbstract;
use Elixir\Dispatcher\DispatcherInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface DBInterface extends DispatcherInterface
{
    /**
     * @return integer
     */
    public function lastInsertId();
    
    /**
     * @return boolean
     */
    public function begin();
    
    /**
     * @return boolean
     */
    public function rollBack();
    
    /**
     * @return boolean
     */
    public function commit();
    
    /**
     * @return boolean
     */
    public function inTransaction();
    
    /**
     * @param string $pTable
     * @return SQLAbstract
     */
    public function createSelect($pTable = null);
    
    /**
     * @param string $pTable
     * @return SQLAbstract
     */
    public function createInsert($pTable = null);
            
    /**
     * @param string $pTable
     * @return SQLAbstract
     */
    public function createDelete($pTable = null);
    
    /**
     * @param string $pTable
     * @return SQLAbstract
     */
    public function createUpdate($pTable = null);
        
    /**
     * @param mixed $pValue
     * @param integer $pType
     * @return mixed
     */
    public function quote($pValue, $pType = null);
    
    /**
     * @param SQLAbstract|string $pSQL
     * @param array $pValues
     * @param array $pOptions
     * @return SetAbstract|boolean
     */
    public function query($pSQL, array $pValues = array(), array $pOptions = array());
}