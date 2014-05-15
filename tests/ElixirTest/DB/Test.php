<?php

namespace ElixirTest\DB;

use Elixir\ClassLoader\Loader;
use Elixir\DB\SQL\Select;
use Elixir\DB\SQL\MySQL\Update;
use Elixir\DB\SQL\MySQL\Delete;
use Elixir\DB\SQL\MySQL\Insert;

class Test extends \PHPUnit_Framework_TestCase
{
    protected $_loader;

    public function __construct()
    {
        require_once __DIR__ . '/../../../src/framework/Elixir/ClassLoader/Loader.php';
        
        $this->_loader = new Loader();
        $this->_loader->addNamespace('ElixirTest', __DIR__ . './../');
        $this->_loader->register();
    }
    
    public function testInsert()
    {
        $insert = new Insert('`test`');
        $insert->ignore(true)
               ->values(['`name`' => 'my name',
                         '`firstname`' => 'my firstname']);
        
        $compare = "INSERT IGNORE INTO `test` (`name`, `firstname`) VALUES ('my name', 'my firstname')";
        $this->assertEquals($compare, preg_replace('/[\n\t\r]/', '', $insert->render()));
    }
    
    public function testSelect()
    {
        $select = new Select('`test`');
        $select->where('`test`.`name` = ?', 'my name')
               ->join('`test2`', function($pSQL)
               {
                    $pSQL->on('`test2`.id = ?', 6);
               })
               ->having(function($pSQL)
               {
                    $pSQL->where('`test`.`firstname` = :firstname', ['firstname' => 'my firstname']);
               })
               ->orderBy('`test2`.id')
               ->quantifier(Select::QUANTIFIER_DISTINCT)
               ->limit(1)
               ->offset(6);
        
        $compare = "SELECT DISTINCT `test`.* FROM `test` INNER JOIN `test2` ON (`test2`.id = 6) WHERE (`test`.`name` = 'my name') HAVING (`test`.`firstname` = 'my firstname') ORDER BY `test2`.id ASC LIMIT 1 OFFSET 6";
        $this->assertEquals($compare, preg_replace('/[\n\t\r]/', '', $select->render()));
    }
    
    public function testUpdate()
    {
        $update = new Update('`test`');
        $update->where('`test`.`type` = ?', 'my type')
               ->set(['`test`.`type`' => 'my new type'])
               ->orderBy('RAND()', null)
               ->limit(1);
        
        $compare = "UPDATE `test` SET `test`.`type` = 'my new type' WHERE (`test`.`type` = 'my type') ORDER BY RAND() LIMIT 1";
        $this->assertEquals($compare, preg_replace('/[\n\t\r]/', '', $update->render()));
    }
    
    public function testDelete()
    {
        $delete = new Delete('`test`');
        $delete->where('`test`.`type` = ?', 'my type');
        
        $compare = "DELETE FROM `test` WHERE (`test`.`type` = 'my type')";
        $this->assertEquals($compare, preg_replace('/[\n\t\r]/', '', $delete->render()));
    }
}
