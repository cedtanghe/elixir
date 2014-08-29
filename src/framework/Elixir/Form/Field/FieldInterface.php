<?php

namespace Elixir\Form\Field;

use Elixir\Dispatcher\DispatcherInterface;
use Elixir\Form\FormInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface FieldInterface extends DispatcherInterface
{
    /**
     * @var integer
     */
    const FILTER_IN = 1;
    
    /**
     * @var integer
     */
    const FILTER_OUT = 2;
    
    /**
     * @var integer
     */
    const FILTER_ALL = 3;
    
    /**
     * @param string $pValue
     */
    public function setName($pValue);
    
    /**
     * @return string
     */
    public function getName();
    
    /**
     * @param mixed $pValue
     */
    public function setHelper($pValue);
    
    /**
     * @return mixed
     */
    public function getHelper();
    
    /**
     * @internal
     * @param FormInterface $pValue
     */
    public function setParent(FormInterface $pValue);
    
    /**
     * @return FormInterface
     */
    public function getParent();
    
    /**
     * @param string $pValue
     */
    public function setLabel($pValue);
    
    /**
     * @return string
     */
    public function getLabel();
    
    /**
     * @param string $pValue
     */
    public function setErrorMessage($pValue);
    
    /**
     * @return string
     */
    public function getErrorMessage();
    
    /**
     * @param boolean $pValue
     */
    public function setRequired($pValue);
    
    /***
     * @return boolean
     */
    public function isRequired();
    
    public function prepare();
    
    /**
     * @return array
     */
    public function getAttributes();
    
    /**
     * @param array $pData
     */
    public function setAttributes(array $pData);
    
    /**
     * @return array
     */
    public function getOptions();
      
    /**
     * @param array $pData
     */
    public function setOptions(array $pData);
    
    /**
     * @param boolean $pValue
     */
    public function setErrorBreak($pValue);
    
    /**
     * @return boolean
     */
    public function isErrorBreak();
    
    /**
     * @return array
     */
    public function getValidators();
    
    /**
     * @param array $pData
     */
    public function setValidators(array $pData);
    
    /**
     * @return array
     */
    public function getFilters();
    
    /**
     * @param array $pData
     */
    public function setFilters(array $pData);
    
    /**
     * @param mixed $pValue
     * @param boolean $pFiltered
     */
    public function setValue($pValue, $pFiltered = true);
    
    /**
     * @param boolean $pFiltered
     * @return mixed
     */
    public function getValue($pFiltered = true);
    
    /**
     * @return boolean
     */
    public function isEligible();
    
    /**
     * @return boolean
     */
    public function isEmpty();
    
    /**
     * @param mixed $pValue
     * @return boolean
     */
    public function isValid($pValue = null);
    
    /**
     * @return boolean
     */
    public function hasError();
    
    /**
     * @return string|array
     */
    public function errors();
    
    public function reset();
}
