<?php

namespace Elixir\DB;

use Elixir\DB\DBInterface;
use Elixir\DB\SQL\SQLFactory;
use Elixir\Dispatcher\Dispatcher;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class DBAbstract extends Dispatcher implements DBInterface
{
    /**
     * @return string
     */
    abstract public function getDriver();

    /**
     * @see DBInterface::createSelect()
     */
    public function createSelect($pTable = null)
    {
       $select = SQLFactory::select($pTable, $this->getDriver());
       $select->setQuoteMethod([$this, 'quote']);
       
       return $select;
    }
    
    /**
     * @see DBInterface::createInsert()
     */
    public function createInsert($pTable = null)
    {
        $insert = SQLFactory::insert($pTable, $this->getDriver());
        $insert->setQuoteMethod([$this, 'quote']);

        return $insert;
    }
    
    /**
     * @see DBInterface::createDelete()
     */
    public function createDelete($pTable = null)
    {
        $delete = SQLFactory::delete($pTable, $this->getDriver());
        $delete->setQuoteMethod([$this, 'quote']);

        return $delete;
    }
    
    /**
     * @see DBInterface::createUpdate()
     */
    public function createUpdate($pTable = null)
    {
        $update = SQLFactory::update($pTable, $this->getDriver());
        $update->setQuoteMethod([$this, 'quote']);

        return $update;
    }
    
    /**
     * @see DBInterface::createTable()
     */
    public function createTable($pTable = null)
    {
        $create = SQLFactory::createTable($pTable, $this->getDriver());
        $create->setQuoteMethod([$this, 'quote']);
        
        return $create;
    }
    
    /**
     * @see DBInterface::createAlterTable()
     */
    public function createAlterTable($pTable = null)
    {
        $alter = SQLFactory::createAlterTable($pTable, $this->getDriver());
        $alter->setQuoteMethod([$this, 'quote']);
        
        return $alter;
    }
    
    /**
     * @see DBInterface::createDrop()
     */
    public function createDropTable($pTable = null)
    {
        return SQLFactory::dropTable($pTable, $this->getDriver());
    }
}
