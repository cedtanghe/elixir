<?php

namespace ElixirTest\Config;

use Elixir\ClassLoader\PSR4;
use Elixir\Config\Config;
use Elixir\Config\Loader\LoaderFactory;
use Elixir\Config\Loader\YAML;
use Elixir\Config\Processor\Filter;
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
        
        LoaderFactory::$loaders['YAML'] = function($config, $options)
        {
            if (substr($config, -4) == '.yml')
            {
                return new YAML($options['environment'], $options['strict'], 'Spyc::YAMLLoad');
            }

            return null;
        };
    }
    
    public function testConfig()
    {
        $config = new Config('test');
        $config->load([__DIR__ . '/../../config/config.php',
                       __DIR__ . '/../../config/config.json',
                       __DIR__ . '/../../config/config.yml']);
        
        $this->assertEquals('new value-2', $config->get('key-2'));
    }
    
    public function testProcessConfig()
    {
        $config = new Config('test');
        $config->load(__DIR__ . '/../../config/config.yml');
        
        $this->assertEquals('{REPLACE}value-4-2', $config->get(['key-4', 'key-4-3']));
        
        $processor = new Filter(new Replace(), ['regex' => '/{REPLACE}/', 'by' => 'VALUE_REPLACED_']);
        $config->addProcessor($processor);
        
        $this->assertEquals('VALUE_REPLACED_value-4-2', $config->get(['key-4', 'key-4-3']));
    }
}
