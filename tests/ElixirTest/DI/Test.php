<?php

namespace ElixirTest\DI;

use Elixir\ClassLoader\PSR4;
use Elixir\DI\ContainerInterface;
use Elixir\DI\Container;

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
    
    public function testAddParameter()
    {
        $container = new Container();
        
        $container->set('param', 'This is a parameter');
        $this->assertEquals('This is a parameter', $container->get('param'));
    }
    
    public function testAddService()
    {
        $container = new Container();
        
        $container->set('param', function($pContainer)
        {
            return 'This is a service';
        });
        
        $this->assertEquals('This is a service', $container->get('param'));
    }
    
    public function testAddServiceWithDefinitionAbstractClass()
    {
        $container = new Container();
        $container->set('param', new Definition());
        
        $this->assertEquals('This is a definition', $container->get('param'));
    }
    
    public function testAddServiceWithOtherParameter()
    {
        $container = new Container();
        
        $container->set('param-1', 'This is a parameter');
        
        $container->set('param-2', function($pContainer)
        {
            return $pContainer->get('param-1');
        });
        
        $this->assertEquals('This is a parameter', $container->get('param-2'));
    }
    
    public function testAddProtectService()
    {
        $container = new Container();
        
        $container->wrap('param', function($pContainer)
        {
            return 'This is a service';
        });
        
        $this->assertTrue($container->get('param') instanceof \Closure);
    }
    
    public function testAddSingletonService()
    {
        $container = new Container();
        
        $container->singleton('param', function($pContainer)
        {
            $std = new \stdClass();
            $std->rand = rand(0, 10);
            
            return $std;
        });
        
        $std1 = $container->get('param');
        $rand = $std1->rand;
        
        $std2 = $container->get('param');
        
        $this->assertEquals($rand, $std2->rand);
    }
    
    public function testExtend()
    {
        $container = new Container();
        
        $data = [];
        $container->set('param', $data);
        
        $container->extend('param', function($pService, $pContainer)
        {
            $pService['data'] = 'This is an extension';
            return $pService;
        });
        
        $service = $container->get('param');
        
        $this->assertEquals('This is an extension', $service['data']);
    }
    
    public function testAddSingletonServiceWithTags()
    {
        $container = new Container();
        
        $container->set('param', function($pContainer)
        {
            return 'This is a service';
        }, 
        ['type' => ContainerInterface::SINGLETON, 'tags' => 'tag']);
        
        $services = array_values($container->findByTag('tag'));
        $this->assertEquals('This is a service', $services[0]);
    }
}
