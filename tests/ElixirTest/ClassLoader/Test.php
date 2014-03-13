<?php

namespace ElixirTest\ClassLoader;

use Elixir\ClassLoader\Loader;

class Test extends \PHPUnit_Framework_TestCase
{
    protected $_loader;

    public function __construct()
    {
        require_once __DIR__ . '/../../../elixir/framework/Elixir/ClassLoader/Loader.php';
        
        $this->_loader = new Loader();
        $this->_loader->register();
    }
    
    public function testClassExist()
    {
        $this->assertTrue($this->_loader->classExist('Elixir\ClassLoader\Loader'));
    }
    
    public function testClassNotExist()
    {
        $this->assertFalse($this->_loader->classExist('Class_NotExist'));
    }
}
