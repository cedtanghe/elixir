<?php

namespace ElixirTest\I18N;

use Elixir\ClassLoader\Loader;
use Elixir\I18N\I18N;
use Elixir\I18N\Plural;

class Test extends \PHPUnit_Framework_TestCase
{
    protected $_loader;

    public function __construct()
    {
        require_once __DIR__ . '/../../../elixir/framework/Elixir/ClassLoader/Loader.php';
        
        $this->_loader = new Loader();
        $this->_loader->addNamespace('ElixirTest', __DIR__ . './../');
        $this->_loader->register();
    }
    
    public function testTranslate()
    {
        $translate = new I18N();
        $translate->setLocale('fr-FR');
        
        $translate->load(array(
                __DIR__ . '/../../languages/translations.mo',
                __DIR__ . '/../../languages/translations.json',
                __DIR__ . '/../../languages/translations.csv'
            ),
            'fr-FR'
        );
        
        $this->assertEquals('Bonjour tout le monde !', $translate->translate('Hello world !'));
        $this->assertEquals('This is json string', $translate->translate('json-key'));
        $this->assertEquals('This is CSV string', $translate->translate('csv-key'));
    }

    public function testPluralize()
    {
        $plural = new Plural();
        $messages = array('There are no dogs', 'There is 1 dog', 'There are {COUNT} dogs');
        
        $this->assertEquals('There are no dogs', $plural->pluralize($messages, -5, 'fr-FR'));
        $this->assertEquals('There is 1 dog', $plural->pluralize($messages, 1, 'fr-FR'));
        $this->assertEquals('There are 5 dogs', $plural->pluralize($messages, 5, 'fr-FR'));
        
        $str = 'I have [no fingers|one finger|{COUNT} fingers] and [{COUNT} leg|{COUNT} legs]';
        
        $this->assertEquals('I have no fingers and 0 leg', $plural->pluralize($str, 0, 'fr-FR'));
        $this->assertEquals('I have one finger and 1 leg', $plural->pluralize($str, 1, 'fr-FR'));
        $this->assertEquals('I have 6 fingers and 6 legs', $plural->pluralize($str, 6, 'fr-FR'));
    }
    
    public function testTransPluralize()
    {
        $translate = new I18N();
        $translate->setLocale('fr-FR');
        $translate->load(__DIR__ . '/../../languages/translations.mo');
        
        $this->assertEquals('Il n\'y a aucun chien', $translate->transPlural('There [are no dogs|is 1 dog|are {COUNT} dogs]', -5));
    }
}
