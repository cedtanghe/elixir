<?php

namespace Elixir\DB\Query;

use Elixir\DB\Query\QueryBuilderInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
trait QueryBuilderTrait 
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

        if (method_exists($this, 'quote'))
        {
            $select->setQuoteMethod([$this, 'quote']);
        }

        return $select;
    }

    /**
     * @see QueryBuilderInterface::createInsert()
     */
    public function createInsert($pTable = null)
    {
        $insert = SQLFactory::insert($pTable, $this->getDriver());
        
        if (method_exists($this, 'quote'))
        {
            $select->setQuoteMethod([$this, 'quote']);
        }

        return $insert;
    }

    /**
     * @see QueryBuilderInterface::createDelete()
     */
    public function createDelete($pTable = null) 
    {
        $delete = SQLFactory::delete($pTable, $this->getDriver());
        
        if (method_exists($this, 'quote'))
        {
            $select->setQuoteMethod([$this, 'quote']);
        }

        return $delete;
    }

    /**
     * @see QueryBuilderInterface::createUpdate()
     */
    public function createUpdate($pTable = null)
    {
        $update = SQLFactory::update($pTable, $this->getDriver());
        
        if (method_exists($this, 'quote'))
        {
            $update->setQuoteMethod([$this, 'quote']);
        }

        return $update;
    }

    /**
     * @see QueryBuilderInterface::createTable()
     */
    public function createTable($pTable = null) 
    {
        $create = SQLFactory::createTable($pTable, $this->getDriver());
        
        if (method_exists($this, 'quote'))
        {
            $create->setQuoteMethod([$this, 'quote']);
        }

        return $create;
    }

    /**
     * @see QueryBuilderInterface::createAlterTable()
     */
    public function createAlterTable($pTable = null)
    {
        $alter = SQLFactory::createAlterTable($pTable, $this->getDriver());
        
        if (method_exists($this, 'quote'))
        {
            $alter->setQuoteMethod([$this, 'quote']);
        }

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
