<?php

namespace ElixirTest\View;

use Elixir\ClassLoader\PSR4;
use Elixir\View\PHP\PHP;
use Elixir\DI\Container;
use Elixir\View\Storage\Str;
use Elixir\View\Manager;

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
    
    public function testPHPFile()
    {
        $view = new PHP();
        $view->setHelperContainer(new Container());
        $view->set('key-1', 'value-1', true);
        $result = $view->render(__DIR__ . '/../../views/testPHP.phtml');
        
        $this->assertEquals('value-1', $view->get('key-1'));
        $this->assertRegExp('/block base/', $result);
    }
    
    public function testPHPStr()
    {
        $view = new PHP();
        $view->setHelperContainer(new Container());
        $view->set('key', 'value', true);
        
        $str = 'My key equals "<?php echo $this->key; ?>"';
        $result = $view->render(new Str($str));
        
        $this->assertEquals('My key equals "value"', $result);
    }
    
    public function testManager()
    {
        $PHP = new PHP();
        $PHP->setHelperContainer(new Container());
        
        $manager = new Manager();
        $manager->registerEngine('PHP', $PHP, '^(phtml|php)$');
        
        $manager->set('key-1', 'value-1', true);
        $result = $manager->render(__DIR__ . '/../../views/testPHP.phtml');
        
        $this->assertEquals('value-1', $manager->get('key-1'));
        $this->assertRegExp('/block base/', $result);
    }
}
