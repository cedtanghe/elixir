<?php

namespace ElixirTest\ClassLoader;

use Elixir\ClassLoader\PSR0;
use Elixir\ClassLoader\PSR4;

class Test extends \PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        require_once __DIR__ . '/../../../src/framework/Elixir/ClassLoader/PSR4.php';
        require_once __DIR__ . '/../../../src/framework/Elixir/ClassLoader/PSR0.php';
    }
    
    public function testClassExistPSR4()
    {
        $loader = new PSR4();
        $loader->register();
        
        $this->assertTrue($loader->classExist('\Elixir\ClassLoader\PSR4'));
    }
    
    public function testClassExistPSR0()
    {
        $loader = new PSR0();
        $loader->addNamespace('Loader', __DIR__ . '/vendor');
        $loader->addNamespace('Elixir', __DIR__ . '/../../../src/framework/');
        $loader->register();
        
        $this->assertTrue($loader->classExist('\Elixir\ClassLoader\PSR4'));
        $this->assertTrue($loader->classExist('Loader_Hello'));
        $this->assertTrue($loader->classExist('Loader_Hello_World'));
        $this->assertTrue($loader->classExist('Loader\Hello\World_You\Hello_You'));
    }
    
    public function testClassNotExistPSR4()
    {
        $loader = new PSR4();
        $loader->register();
        
        $this->assertFalse($loader->classExist('Class_NotExist'));
    }
    
    public function testClassNotExistPSR0()
    {
        $loader = new PSR0();
        $loader->register();
        
        $this->assertFalse($loader->classExist('Class_NotExist'));
    }
}
