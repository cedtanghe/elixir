<?php

namespace Elixir\DB\Query;

use Elixir\DB\Query\QueryBuilderInterface;
use Elixir\DB\Query\SQL\SQLFactory;

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
    public function createSelect($table = null) 
    {
        $select = SQLFactory::select($table, $this->getDriver());

        if (method_exists($this, 'quote')) 
        {
            $select->setQuoteMethod([$this, 'quote']);
        }

        return $select;
    }

    /**
     * @see QueryBuilderInterface::createInsert()
     */
    public function createInsert($table = null)
    {
        $insert = SQLFactory::insert($table, $this->getDriver());

        if (method_exists($this, 'quote')) 
        {
            $insert->setQuoteMethod([$this, 'quote']);
        }

        return $insert;
    }

    /**
     * @see QueryBuilderInterface::createDelete()
     */
    public function createDelete($table = null) 
    {
        $delete = SQLFactory::delete($table, $this->getDriver());

        if (method_exists($this, 'quote')) 
        {
            $delete->setQuoteMethod([$this, 'quote']);
        }

        return $delete;
    }

    /**
     * @see QueryBuilderInterface::createUpdate()
     */
    public function createUpdate($table = null)
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
    public function createTable($table = null) 
    {
        $create = SQLFactory::createTable($table, $this->getDriver());

        if (method_exists($this, 'quote')) 
        {
            $create->setQuoteMethod([$this, 'quote']);
        }

        return $create;
    }

    /**
     * @see QueryBuilderInterface::createAlterTable()
     */
    public function createAlterTable($table = null) 
    {
        $alter = SQLFactory::createAlterTable($table, $this->getDriver());

        if (method_exists($this, 'quote'))
        {
            $alter->setQuoteMethod([$this, 'quote']);
        }

        return $alter;
    }

    /**
     * @see QueryBuilderInterface::createDropTable()
     */
    public function createDropTable($table = null) 
    {
        return SQLFactory::dropTable($table, $this->getDriver());
    }

    /**
     * @see QueryBuilderInterface::createTruncateTable()
     */
    public function createTruncateTable($table = null) 
    {
        return SQLFactory::truncateTable($table, $this->getDriver());
    }
}
