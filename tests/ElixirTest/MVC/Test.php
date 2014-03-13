<?php

namespace ElixirTest\MVC;

use Elixir\ClassLoader\Loader;
use Elixir\MVC\Application;
use Elixir\DI\Container;
use Elixir\HTTP\RequestFactory;
use Elixir\MVC\Controller\ControllerResolver;
use Elixir\Module\Application\Bootstrap;
use A\Bootstrap as ABootstrap;
use B\Bootstrap as BBootstrap;
use C\Bootstrap as CBootstrap;

class Test extends \PHPUnit_Framework_TestCase
{
    protected $_loader;

    public function __construct()
    {
        require_once __DIR__ . '/../../../src/framework/Elixir/ClassLoader/Loader.php';
        
        $this->_loader = new Loader();
        $this->_loader->addNamespace('ElixirTest', __DIR__ . './../');
        $this->_loader->addNamespace('Elixir\Module', __DIR__ . '/../../../elixir/modules/');
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
