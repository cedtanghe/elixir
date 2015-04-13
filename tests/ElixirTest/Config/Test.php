<?php

namespace ElixirTest\Config;

use Elixir\ClassLoader\PSR4;
use Elixir\Config\Cache\Compiled;
use Elixir\Config\Config;
use Elixir\Config\Loader\LoaderFactory;
use Elixir\Config\Processor\Filter;
use Elixir\Config\Writer\WriterFactory;
use Elixir\Filter\Replace;

class Test extends \PHPUnit_Framework_TestCase
{
    protected $_loader;

    public function __construct()
    {
        require_once __DIR__ . '/../../../src/framework/Elixir/ClassLoader/PSR4.php';
        require_once 'spyc.php4';
        
        $this->_loader = new PSR4();
        $this->_loader->addNamespace('ElixirTest', __DIR__ . './../');
        $this->_loader->register();
        
        LoaderFactory::$factories['YAML'] = function($config, $options)
        {
            if (substr($config, -4) == '.yml')
            {
                return new \Elixir\Config\Loader\YAML($options['environment'], $options['strict'], 'Spyc::YAMLLoad');
            }

            return null;
        };
        
        WriterFactory::$factories['YAML'] = function($file)
        {
            if (substr($file, -4) == '.yml')
            {
                return new \Elixir\Config\Writer\YAML(null, 'Spyc::YAMLDump');
            }

            return null;
        };
    }
    
    public function testConfig()
    {
        $time = microtime();
        $config = new Config('test');
        
        //$config->setCacheStrategy(new PreservePHP(__DIR__ . '/../../config/', 'cache', false));
        //$config->setCacheStrategy(new Grouped(__DIR__ . '/../../config/', 'cache'));
        $config->setCacheStrategy(new Compiled(__DIR__ . '/../../config/', 'cache.yml'));
        
        $config->load([__DIR__ . '/../../config/config.php',
                       __DIR__ . '/../../config/config.json',
                       __DIR__ . '/../../config/config.yml']);
        
        $config->exportToCache();
        echo $time - microtime();
        $this->assertEquals('new value-2', $config->get('key-2'));
    }
    
    public function testProcessConfig()
    {
        $config = new Config('test');
        $config->load(__DIR__ . '/../../config/config.yml');
        
        $this->assertEquals('{REPLACE}value-4-2', $config->get(['key-4', 'key-4-3']));
        
        $processor = new Filter(new Replace(), ['regex' => '/{REPLACE}/', 'by' => 'VALUE_REPLACED_']);
        $config->setProcessor($processor);
        
        $this->assertEquals('VALUE_REPLACED_value-4-2', $config->get(['key-4', 'key-4-3']));
    }
}
