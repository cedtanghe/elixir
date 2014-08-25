<?php

namespace Elixir\Form;

use Elixir\Dispatcher\DispatcherInterface;
use Elixir\Form\Field\FieldInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface FormInterface extends DispatcherInterface
{
    /**
     * @var string
     */
    const GET = 'get';
    
    /**
     * @var string
     */
    const POST = 'post';
    
    /**
     * @var string
     */
    const PUT = 'put';
    
    /**
     * @var string
     */
    const DELETE = 'delete';
    
    /**
     * @var string
     */
    const METHOD_FIELD = '_method';
    
    /**
     * @var string
     */
    const ENCTYPE_URLENCODED = 'application/x-www-form-urlencoded';
    
    /**
     * @var string
     */
    const ENCTYPE_MULTIPART = 'multipart/form-data';
    
    /**
     * @var string
     */
    const ENCTYPE_TEXT_PLAIN = 'text/plain';
    
    /**
     * @var integer
     */
    const ONLY_FIELDS = 1;
    
    /**
     * @var integer
     */
    const ONLY_FORMS = 2;
    
    /**
     * @var integer
     */
    const ALL_ITEMS = 4;
    
    /**
     * @var integer
     */
    const ALL_FIELDS = 8;
    
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
    public function setName($pValue);
    
    /**
     * @return string
     */
    public function getName();
    
    /**
     * @param callable $pValue
     */
    public function setHelper(callable $pValue);
    
    /**
     * @return callable
     */
    public function getHelper();
    
    /**
     * @param string $pValue
     */
    public function setErrorMessage($pValue);
    
    /**
     * @return string
     */
    public function getErrorMessage();
    
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
     * @param FieldInterface|FormInterface $pItem
     */
    public function add($pItem);
    
    /**
     * @param string $pName
     * @param boolean $pUseSubForms
     */
    public function remove($pName, $pUseSubForms = false);
    
    /**
     * @param integer $pMask
     * @return array
     */
    public function gets($pMask = self::ALL_FIELDS);
    
    /**
     * @param array $pData
     */
    public function sets(array $pData);
    
    
    public function prepare();
    
     /**
     * @return boolean
     */
    public function isPrepared();
    
    /**
     * @return boolean
     */
    public function isEmpty();
    
    /**
     * @param array $pData
     * @param boolean $pFiltered
     */
    public function bind(array $pData, $pFiltered = true);
    
    /**
     * @param array $pData
     * @return boolean
     */
    public function submit(array $pData = null);
    
    /**
     * @param boolean $pFiltered
     * @return array
     */
    public function values($pFiltered = true);
    
    /**
     * @return boolean
     */
    public function hasError();
    
    /**
     * @return string|array
     */
    public function errors();
    
    /**
     * @param array $pOmit
     */
    public function reset(array $pOmit = []);
}
