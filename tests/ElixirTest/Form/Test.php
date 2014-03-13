<?php

namespace ElixirTest\Form;

use Elixir\ClassLoader\Loader;
use Elixir\Form\Form;
use Elixir\Form\FormFactory;
use Elixir\Form\Field\Input;
use Elixir\Validator\NotEmpty;

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
    
    public function testForm()
    {
        $form = new Form();
        $form->setName('my-form');
        $form->setAttribute('id', 'my-id');
        $form->setOption('test', 'value');
        $form->setErrorMessage('An error has been detected');
        
        $input = new Input('item-input');
        $input->addValidator(new NotEmpty());
        $input->setRequired(false);
        
        $form->add($input);
        
        $this->assertEquals('my-form', $form->getName());
        $this->assertTrue($form->submit(array('iten-not-exist' => 'ok')));
        
        $input->setRequired(true);
        
        $this->assertFalse($form->submit(array('iten-not-exist' => 'ok')));
        $this->assertTrue($form->submit(array('item-input' => 'ok')));
    }
    
    public function testFormWithFactory()
    {
        $form = FormFactory::createForm(array('name' => 'my-form',
                                              'attributes' => array('id' => 'my-id'),
                                              'options' => array('test' => 'value'),
                                              'errorMessage' => 'An error has been detected',
                                              'items' => array('item-input' => array('type' => 'Elixir\Form\Field\Input',
                                                                                     'required' => false,
                                                                                     'validators' => array(new NotEmpty())))));
        
        $this->assertEquals('my-form', $form->getName());
        $this->assertTrue($form->submit(array('iten-not-exist' => 'ok')));
        
        $input = $form->get('item-input');
        $input->setRequired(true);
        
        $this->assertFalse($form->submit(array('iten-not-exist' => 'ok')));
        $this->assertTrue($form->submit(array('item-input' => 'ok')));
    }
}
