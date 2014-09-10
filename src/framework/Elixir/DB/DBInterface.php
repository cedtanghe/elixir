<?php

namespace Elixir\DB;

use Elixir\DB\Result\SetAbstract;
use Elixir\DB\SQL\SQLInterface;
use Elixir\Dispatcher\DispatcherInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
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
     * @param mixed $pValue
     * @param integer $pType
     * @return mixed
     */
    public function quote($pValue, $pType = null);
    
    /**
     * @param SQLInterface|string $pSQL
     * @return integer
     */
    public function exec($pSQL);
    
    /**
     * @param SQLInterface|string $pSQL
     * @param array $pValues
     * @param array $pOptions
     * @return SetAbstract|boolean
     */
    public function query($pSQL, array $pValues = [], array $pOptions = []);
}
