<?php

namespace ElixirTest\Cookies;

use Elixir\ClassLoader\Loader;
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
    
    public function testCreateCookie()
    {
        $date = \DateTime::createFromFormat('D, d-M-Y H:i:s \G\M\T', 'Tue, 14-May-2013 16:12:09 GMT', new \DateTimeZone('GMT'));
        $cookie = new Cookie('test', 'cookie value', $date->getTimestamp(), '/', 'www.Elixir.com', true, true);
        
        $this->assertEquals('test=cookie%20value; expires=Tue, 14-May-2013 16:12:09 GMT; path=/; domain=.Elixir.com; secure; httponly', $cookie->toString());
    }
    
    public function testParseCookie()
    {
        $cookie = Cookie::fromString('test=cookie%20value; expires=Tue, 14-May-2013 16:12:09 GMT; path=/; domain=.Elixir.com; httponly');
        
        $this->assertEquals('test', $cookie->getName());
        $this->assertEquals('cookie value', $cookie->getValue());
        $this->assertEquals(true, $cookie->isHTTPOnly());
        $this->assertEquals(false, $cookie->isSecure());
        $this->assertEquals(1368547929, $cookie->getExpires());
    }
}
