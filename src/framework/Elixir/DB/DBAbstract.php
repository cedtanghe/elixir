<?php

namespace Elixir\DB;

use Elixir\DB\DBInterface;
use Elixir\DB\QueryBuilderInterface;
use Elixir\DB\SQL\SQLFactory;
use Elixir\Dispatcher\Dispatcher;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class DBAbstract extends Dispatcher implements DBInterface, QueryBuilderInterface
{
    /**
     * @return string
     */
    abstract public function getDriver();

    /**
     * @see QueryBuilderInterface::createSelect()
     */
    public function createSelect($pTable = null)
    {
       $select = SQLFactory::select($pTable, $this->getDriver());
       $select->setQuoteMethod([$this, 'quote']);
       
       return $select;
    }
    
    /**
     * @see QueryBuilderInterface::createInsert()
     */
    public function createInsert($pTable = null)
    {
        $insert = SQLFactory::insert($pTable, $this->getDriver());
        $insert->setQuoteMethod([$this, 'quote']);

        return $insert;
    }
    
    /**
     * @see QueryBuilderInterface::createDelete()
     */
    public function createDelete($pTable = null)
    {
        $delete = SQLFactory::delete($pTable, $this->getDriver());
        $delete->setQuoteMethod([$this, 'quote']);

        return $delete;
    }
    
    /**
     * @see QueryBuilderInterface::createUpdate()
     */
    public function createUpdate($pTable = null)
    {
        $update = SQLFactory::update($pTable, $this->getDriver());
        $update->setQuoteMethod([$this, 'quote']);

        return $update;
    }
    
    /**
     * @see QueryBuilderInterface::createTable()
     */
    public function createTable($pTable = null)
    {
        $create = SQLFactory::createTable($pTable, $this->getDriver());
        $create->setQuoteMethod([$this, 'quote']);
        
        return $create;
    }
    
    /**
     * @see QueryBuilderInterface::createAlterTable()
     */
    public function createAlterTable($pTable = null)
    {
        $alter = SQLFactory::createAlterTable($pTable, $this->getDriver());
        $alter->setQuoteMethod([$this, 'quote']);
        
        return $alter;
    }
    
    /**
     * @see QueryBuilderInterface::createDropTable()
     */
    public function createDropTable($pTable = null)
    {
        return SQLFactory::dropTable($pTable, $this->getDriver());
    }
    
     /**
     * @see QueryBuilderInterface::createTruncateTable()
     */
    public function createTruncateTable($pTable = null)
    {
        return SQLFactory::truncateTable($pTable, $this->getDriver());
    }
}
