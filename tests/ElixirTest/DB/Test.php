<?php

namespace ElixirTest\DB;

use Elixir\ClassLoader\Loader;
use Elixir\DB\SQL\Column;
use Elixir\DB\SQL\ColumnFactory;
use Elixir\DB\SQL\Constraint;
use Elixir\DB\SQL\ConstraintFactory;
use Elixir\DB\SQL\MySQL\Create;
use Elixir\DB\SQL\MySQL\Delete;
use Elixir\DB\SQL\MySQL\Drop;
use Elixir\DB\SQL\MySQL\Insert;
use Elixir\DB\SQL\MySQL\Update;
use Elixir\DB\SQL\Select;

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
    
    public function testCreate()
    {
        $create = new Create('test');
        $create->ifNotExists(true);
        $create->column(ColumnFactory::int('id', 11, Column::UNSIGNED, true));
        $create->column(
            ColumnFactory::varchar('name', 255, true)
            ->setCollating('utf8_general_ci')
            ->setComment('Comment')
        );
        
        $create->constraint(ConstraintFactory::unique('name'));
        $create->constraint(
            ConstraintFactory::foreign(
                'id',
                'test2', 
                'id', 
                null, 
                Constraint::REFERENCE_CASCADE,
                Constraint::REFERENCE_CASCADE
            )
        );
        
        $create->option(Create::OPTION_ENGINE, Create::ENGINE_INNODB);
        $create->option(Create::OPTION_CHARSET, Create::CHARSET_UTF8);
        
        $compare = "CREATE TABLE IF NOT EXISTS test (id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT , name VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'Comment', UNIQUE KEY name(name), CONSTRAINT test2_id_id FOREIGN KEY (id) REFERENCES test2(id) ON DELETE CASCADE ON UPDATE CASCADE, PRIMARY KEY (id)) ENGINE = 'InnoDB' DEFAULT CHARSET = 'utf8'";
        $this->assertEquals($compare, preg_replace('/[\n\t\r]/', '', $create));
    }
    
    public function testDrop()
    {
        $drop = new Drop('test');
        $drop->ifExists(true);
        
        $compare = "DROP TABLE IF EXISTS test";
        $this->assertEquals($compare, preg_replace('/[\n\t\r]/', '', $drop->render()));
    }
}