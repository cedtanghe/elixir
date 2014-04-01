<?php

namespace Elixir\Form\Extension;

use Elixir\Form\Field\FieldInterface;
use Elixir\Form\FormEvent;
use Elixir\Form\FormInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Reference implements ExtensionInterface
{
    /**
     * @var FormInterface 
     */
    protected $_form;
    
    /**
     * @var string
     */
    protected $_inputReference;

    /**
     * @var string
     */
    protected $_format;
    
    /**
     * @var array 
     */
    protected $_references = array();
    
    /**
     * @param FieldInterface $pReference
     * @param string $pFormat
     */
    public function __construct(FieldInterface $pInputReference, $pFormat)
    {
        $this->_inputReference = $pInputReference;
        $this->_format = $pFormat;
    }
    
    /**
     * @return string
     */
    public function getInputReference()
    {
        return $this->_inputReference;
    }
    
    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * @see ExtensionInterface::load()
     */
    public function load(FormInterface $pForm) 
    {
        $this->_form = $pForm;
        $this->_form->addListener(FormEvent::PRE_BIND, array($this, 'onPreBind'));
        $this->_form->addListener(FormEvent::PRE_SUBMIT, array($this, 'onPreSubmit'));
        $this->_form->addListener(FormEvent::PRE_VALUES, array($this, 'onPreValues'));
    }
    
    /**
     * @internal
     * @param FormEvent $e
     */
    public function onPreBind(FormEvent $e)
    {
        $data = $e->getData();
        
        if(isset($data[$this->_inputReference->getName()]))
        {
            $this->_references = array();
            
            $me = $this;
            $pattern = preg_replace_callback('/{([^}]+)}/', function($matches) use($me) 
            {
                return '(?P<' . $me->protect($matches[1]) . '>.*)';
            },
            $this->_format);
            
            if(preg_match('/^' . preg_quote($pattern, '/') . '$/', $data[$this->_inputReference->getName()], $matches))
            {
               foreach($matches as $key => $value)
                {
                    if(isset($this->_references[$key]))
                    {
                        $this->_form->get($this->_references[$key], true)->setValue($value, false);
                    }
                }
            }
        }
    }
    
    /**
     * @param string $pValue
     * @return string
     */
    public function protect($pValue)
    {
        $key = str_replace(str_split('.\+*?[^]$(){}=!<>|:-%'), '', $pValue);
        $this->_references[$key] = $pValue;
        
        return $key;
    }
    
    /**
     * @internal
     * @param FormEvent $e
     */
    public function onPreSubmit(FormEvent $e)
    {
        $data = $e->getData();
        
        if(null !== $data)
        {
            unset($data[$this->_inputReference->getName()]);
            $e->setData($data);
        }
        
        $this->onPreValues($e);
    }
    
    /**
     * @internal
     * @param FormEvent $e
     */
    public function onPreValues(FormEvent $e)
    {
        $eligible = true;
        
        $form = $this->_form;
        $value = preg_replace_callback('/{([^}]+)}/', function($matches) use($form, &$eligible) 
        {
            $item = $form->get($matches[1], true);
            
            if(!$item->isEligible())
            {
                $eligible = false;
            }
            
            return $form->get($matches[1], true)->getValue(true);
        },
        $this->_format);
        
        $this->_inputReference->setValue($eligible ? $value : null, false);
    }
    
    /**
     * @see ExtensionInterface::unload()
     */
    public function unload() 
    {
        if(null !== $this->_form)
        {
            $this->_form->removeListener(FormEvent::PRE_BIND, array($this, 'onPreBind'));
            $this->_form->removeListener(FormEvent::PRE_SUBMIT, array($this, 'onPreSubmit'));
            $this->_form->removeListener(FormEvent::PRE_VALUES, array($this, 'onPreValues'));
            $this->_form = null;
        }
    }
}