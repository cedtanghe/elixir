<?php

namespace ElixirTest\Cache;

use Elixir\Cache\APC;
use Elixir\Cache\File;
use Elixir\ClassLoader\PSR4;

class Test extends \PHPUnit_Framework_TestCase
{
    protected $_loader;

    public function __construct()
    {
        require_once __DIR__ . '/../../../src/framework/Elixir/ClassLoader/PSR4.php';
        
        $this->_loader = new PSR4();
        $this->_loader->addNamespace('ElixirTest', __DIR__ . './../');
        $this->_loader->register();
    }
    
    public function testAPC()
    {
        if(!extension_loaded('apc'))
        {
            return;
        }
        
        $cache = new APC('debug');
        
        $cache->set('test-1', 'value 1');
        $this->assertEquals('value 1', $cache->get('test-1'));
        
        $cache->set('test-2', 'value 2', 1);
        sleep(2);
        $this->assertEquals(null, $cache->get('test-2'));
        
        $cache->remove('test-1');
        $this->assertEquals(null, $cache->get('test-1'));
        
        $cache->set('test-3', 'value 3');
        $cache->clear();
        $this->assertEquals(null, $cache->get('test-3'));
    }
    
    public function testFile()
    {
        $cache = new File('debug', __DIR__ . '/../../cache');
        
        $cache->set('test-1', 'value 1');
        $this->assertEquals('value 1', $cache->get('test-1'));
        
        $cache->set('test-2', 'value 2', 1);
        sleep(2);
        $this->assertEquals(null, $cache->get('test-2'));
        
        $cache->remove('test-1');
        $this->assertEquals(null, $cache->get('test-1'));
        
        $cache->set('test-3', 'value 3');
        $cache->clear();
        $this->assertEquals(null, $cache->get('test-3'));
    }
}
