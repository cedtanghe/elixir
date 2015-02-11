<?php

namespace ElixirTest\MVC;

use A\Bootstrap as ABootstrap;
use B\Bootstrap as BBootstrap;
use C\Bootstrap as CBootstrap;
use Elixir\ClassLoader\PSR4;
use Elixir\DI\Container;
use Elixir\HTTP\RequestFactory;
use Elixir\Module\AppBase\Bootstrap;
use Elixir\MVC\Application;
use Elixir\MVC\Controller\ControllerResolver;

class Test extends \PHPUnit_Framework_TestCase
{
    protected $_loader;

    public function __construct()
    {
        require_once __DIR__ . '/../../../src/framework/Elixir/ClassLoader/PSR4.php';
        
        $this->_loader = new PSR4();
        $this->_loader->addNamespace('ElixirTest', __DIR__ . './../');
        $this->_loader->addNamespace('A', __DIR__ . '/../../modules/A/');
        $this->_loader->addNamespace('B', __DIR__ . '/../../modules/B/');
        $this->_loader->addNamespace('C', __DIR__ . '/../../modules/C/');
        $this->_loader->register();
    }
    
    public function testApplication()
    {
        $application = new Application(new Container());
        $application->setControllerResolver(new ControllerResolver());
        $application->addModule(new Bootstrap());
        $application->addModule(new ABootstrap());
        $application->addModule(new BBootstrap());
        $application->addModule(new CBootstrap());
        $application->boot();
        
        $request = RequestFactory::create();
        $request->setURL('/a/index/index');
        
        $response = $application->handle($request);
        
        $this->assertEquals('Hello world', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
    }
    
    public function testForward()
    {
        $application = new Application(new Container());
        $application->setControllerResolver(new ControllerResolver());
        $application->addModule(new Bootstrap());
        $application->addModule(new ABootstrap());
        $application->addModule(new BBootstrap());
        $application->addModule(new CBootstrap());
        $application->boot();
        
        $request = RequestFactory::create();
        $request->setURL('/c/index/index');
        
        $response = $application->handle($request);
        
        $this->assertEquals('Hello world from module "A"', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
    }
}
