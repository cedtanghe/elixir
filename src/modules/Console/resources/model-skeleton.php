<?php

namespace {NAMESPACE}\Model;

use Elixir\DB\ORM\ModelAbstract;
use Elixir\DI\ContainerInterface;
use Elixir\MVC\Application;

class {MODEL} extends ModelAbstract 
{
    public function __construct(ContainerInterface $pManager = null, array $pData = array()) 
    {
        $this->_table = '';
        $this->_primaryKey = 'id';
        $this->_autoIncrement = true;
        
        parent::__construct($pManager ?: Application::$registry, $pData);
    }

    protected function defineColumns() 
    {
    }
}