<?php

namespace ElixirTest\Config;

use Elixir\ClassLoader\Loader;
use Elixir\Config\Config;
use Elixir\Config\Processor\Filter;
use Elixir\Filter\Replace;

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
    
    public function testConfig()
    {
        $config = new Config('test');
        $config->load(array(__DIR__ . '/../../config/config.php',
                            __DIR__ . '/../../config/config.json',
                            __DIR__ . '/../../config/config.xml'));
        
        $this->assertEquals('new value-2', $config->get('key-2'));
    }
    
    public function testProcessConfig()
    {
        $config = new Config('test');
        $config->load(__DIR__ . '/../../config/config.xml');
        
        $this->assertEquals('{REPLACE}value-4-2', $config->get(array('key-4', 'key-4-3')));
        
        $processor = new Filter(new Replace(), array('regex' => '/{REPLACE}/', 'by' => 'VALUE_REPLACED_'));
        $config->addProcessor($processor);
        
        $this->assertEquals('VALUE_REPLACED_value-4-2', $config->get(array('key-4', 'key-4-3')));
    }
}
