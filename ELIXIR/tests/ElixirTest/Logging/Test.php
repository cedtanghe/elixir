<?php

namespace ElixirTest\Logging;

use Elixir\ClassLoader\PSR4;
use Elixir\Logging\Logger;
use Elixir\Logging\Writer\File;

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
    
    public function testLog()
    {
        $logger = new Logger();
        $logger->addWriter(new File(__DIR__ . '/../../logs/logs.txt'));
        $logger->lock(Logger::ERR);
        
        $logger->clear();
        $logger->info('value info');
        $logger->error('value error');
        
        $this->assertTrue(false !== strpos(file_get_contents(__DIR__ . '/../../logs/logs.txt'), 'value info'));
        $this->assertFalse(strpos(file_get_contents(__DIR__ . '/../../logs/logs.txt'), 'value error'));
    }
}
