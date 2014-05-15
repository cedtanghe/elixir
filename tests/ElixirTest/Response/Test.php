<?php

namespace ElixirTest\Response;

use Elixir\ClassLoader\Loader;
use Elixir\HTTP\RequestFactory;
use Elixir\HTTP\Response;
use Elixir\HTTP\ResponseFactory;
use Elixir\HTTP\Cookie;

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
    
    public function testCreateSimpleResponse()
    {
        $response = ResponseFactory::create('Hello world', 200, 'HTTP/1.1', []);
        
        $this->assertEquals('Hello world', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
    }
    
    public function testCreateResponseFromString()
    {
        $response = ResponseFactory::create('Page not found', 404, 'HTTP/1.1', ['Cache-Control' => ['public' => true]]);
        $response->getHeaders()->setCookie(new Cookie('test', 'cookie value'));
        
        $response = Response::fromString($response);
        
        $this->assertEquals('Page not found', $response->getContent());
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Not Found', $response->getReasonPhrase());
    }
}
