<?php

namespace Elixir\Form\Extension;

use Elixir\Form\Extension\ExtensionInterface;
use Elixir\Form\FormFactory;
use Elixir\Form\FormInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class CSRF implements ExtensionInterface
{
    /**
     * @var string
     */
    const DEFAULT_NAME = 'csrf_token';
    
    /**
     * @var FormInterface 
     */
    protected $_form;
    
    /**
     * @var string
     */
    protected $_name;
    
    /**
     * @var array
     */
    protected $_options = [];

    /**
     * @param string $pName
     * @param array $pOptions
     */
    public function __construct($pName = self::DEFAULT_NAME, array $pOptions = [])
    {
        $this->_name = $pName;
        $this->_options = $pOptions;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @see ExtensionInterface::load()
     */
    public function load(FormInterface $pForm) 
    {
        $this->_form = $pForm;
        
        $this->_form->add(FormFactory::createField([
            'type' => 'Elixir\Form\Field\CSRF',
            'name' => $this->_name,
            'required' => true,
            'CSRFValidatorOptions' => $this->_options
        ]));
    }
    
    /**
     * @see ExtensionInterface::unload()
     */
    public function unload() 
    {
        if(null !== $this->_form)
        {
            $this->_form->remove($this->_name);
            $this->_form = null;
        }
    }
}