<?php

namespace ElixirTest\Session;

use Elixir\ClassLoader\Loader;
use Elixir\HTTP\Session\Session;

class Test extends \PHPUnit_Framework_TestCase
{
    protected $_loader;

    public function __construct()
    {
        require_once __DIR__ . '/../../../src/framework/Elixir/ClassLoader/Loader.php';
        
        $this->_loader = new Loader();
        $this->_loader->addNamespace('ElixirTest', __DIR__ . './../');
        $this->_loader->register();
        
        if(null === Session::instance())
        {
            new Session();
        }
        
        if(!Session::instance()->exist())
        {
            Session::instance()->setName('ElixirTest');
            Session::instance()->start();
            Session::instance()->clear();
        }
    }
    
    public function testAddKey()
    {
        Session::instance()->set(array('container', 'key'), 'value');
        $this->assertEquals(array('key' => 'value'), Session::instance()->get('container'));
        
        Session::instance()->set(array('container', 'key'), 'new value');
        $this->assertEquals('new value', Session::instance()->get(array('container', 'key')));
    }
    
    public function testRemoveKey()
    {
        Session::instance()->set(array('container', 'key'), 'value');
        Session::instance()->remove(array('container', 'key'));
        
        $this->assertEquals(array(), Session::instance()->get('container'));
    }
    
    public function testFlashKey()
    {
        Session::instance()->flash('key', 'value');
        
        $this->assertEquals('value', Session::instance()->flash('key'));
        $this->assertEquals(null, Session::instance()->flash('key'));
        $this->assertEquals(array(), Session::instance()->flash());
    }
}
