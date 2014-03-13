<?php

namespace ElixirTest\I18N;

use Elixir\ClassLoader\Loader;
use Elixir\Security\Crypt;

class Security extends \PHPUnit_Framework_TestCase
{
    protected $_loader;

    public function __construct()
    {
        require_once __DIR__ . '/../../../elixir/framework/Elixir/ClassLoader/Loader.php';
        
        $this->_loader = new Loader();
        $this->_loader->addNamespace('ElixirTest', __DIR__ . './../');
        $this->_loader->register();
    }
    
    public function testCript()
    {
        $str = 'Hello world !';
        $cript = new Crypt(hash('sha256', 'secret-phrase', true));
        
        $this->assertEquals($str, $cript->decrypt($cript->encrypt($str)));
    }
}
