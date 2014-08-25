<?php

namespace Elixir\Form\Field;

use Elixir\Form\Field\FieldAbstract;
use Elixir\Form\Field\FieldEvent;
use Elixir\Form\FormInterface;
use Elixir\HTTP\Uploader;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class File extends FieldAbstract
{
    /**
     * @var string
     */
    const RECEIVE = 'receive';
    
    /**
     * @var Uploader 
     */
    protected $_uploader;

    /**
     * @see FieldAbstract::__construct()
     */
    public function __construct($pName = null)
    {
        parent::__construct($pName);
        
        $this->_helper = 'file';
        $this->setAttribute('type', 'file');
    }
    
    /**
     * @see FieldAbstract::setParent()
     */
    public function setParent(FormInterface $pValue) 
    {
        parent::setParent($pValue);
        
        $this->_parent->setAttributes(array_merge($this->_parent->getAttributes(),
                                                  ['enctype' => FormInterface::ENCTYPE_MULTIPART]));
    }
    
    /**
     * @see FieldAbstract::setErrorBreak()
     */
    public function setErrorBreak($pValue)
    {
        parent::setErrorBreak($pValue);
        $this->getUploader()->setErrorBreak($this->_errorBreak);
    }

    /**
     * @return Uploader 
     */
    public function getUploader()
    {
        if(null === $this->_uploader)
        {
            $this->setUploader(new Uploader());
            $this->_uploader->setErrorBreak($this->_errorBreak);
        }
        
        return $this->_uploader;
    }
    
    /**
     * @param Uploader $pValue
     */
    public function setUploader(Uploader $pValue)
    {
        $this->_uploader = $pValue;
        $this->_uploader->setName($this->getName());
    }
    
    /**
     * @param string $pId
     * @param string $pValue
     */
    public function setAPCUploadProgressData($pId, $pValue = null)
    {
        $this->setOption('APC_UPLOAD_PROGRESS_DATA', ['id' => $pId, 'value' => $pValue ? $pValue : uniqid()]);
    }
    
    /**
     * @return array|null
     */
    public function getAPCUploadProgressData()
    {
        return $this->getOption('APC_UPLOAD_PROGRESS_DATA');
    }
    
    /**
     * @param integer $pValue
     */
    public function setMaxFileSize($pValue)
    {
        $this->setOption('MAX_FILE_SIZE', $pValue);
    }
    
    /**
     * @return integer|null
     */
    public function getMaxFileSize()
    {
        return $this->getOption('MAX_FILE_SIZE');
    }
    
    /**
     * @see FieldAbstract::setAttribute()
     */
    public function setAttribute($pKey, $pValue) 
    {
        if($pKey == 'type')
        {
            $pValue = 'file';
        }
        
        parent::setAttribute($pKey, $pValue);
    }

    /**
     * @see FieldAbstract::removeAttribute()
     * @throws \LogicException
     */
    public function removeAttribute($pKey)
    {
        if($pKey == 'type')
        {
            throw new \LogicException('You can not delete the option "type".');
        }
        
        parent::removeAttribute($pKey);
    }
    
    /**
     * @see FieldAbstract::setAttributes()
     */
    public function setAttributes(array $pData)
    {
        $pData['type'] = 'file';
        parent::setAttributes($pData);
    }
    
    /**
     * @see FieldAbstract::setValue()
     */
    public function setValue($pValue, $pFiltered = true)
    {
        $this->_value = $pValue;
    }
    
    /**
     * @see FieldAbstract::getValue()
     */
    public function getValue($pFiltered = true)
    {
        return $this->_value;
    }
    
    /**
     * @see FieldAbstract::isEmpty()
     */
    public function isEmpty()
    {
        return !$this->getUploader()->isUploaded();
    }
    
    /**
     * @see FieldAbstract::isValid()
     */
    public function isValid($pValue = null) 
    {
        $this->_errors = [];
        
        $event = new FieldEvent(FieldEvent::PRE_VALIDATION, $this->_value);
        $this->dispatch($event);
        $this->_value = $event->getValue();
        
        if($this->isEligible())
        {
            $this->getUploader()->setValidators($this->_validators);
            
            if(!$this->getUploader()->isValid())
            {
                $this->_errors = (array)$this->getUploader()->errors();
            }
        }
        
        return !$this->hasError();
    }
    
    /**
     * @return boolean
     */
    public function hasMultipleFiles()
    {
        return $this->getUploader()->hasMultipleFiles();
    }
    
    /**
     * @return boolean
     */
    public function receive()
    {
        $filters = [];
        
        foreach($this->_filters as $data)
        {
            $filters[] = $data;
        }
        
        $this->getUploader()->setFilters($filters);
        $receive = $this->getUploader()->receive();
        
        if($receive)
        {
            $this->_value = $this->getUploader()->getFileName();
        }
        
        return $receive;
    }

    public function reset()
    {
        $this->getUploader()->reset();
        $this->_errors = [];
        $this->_value = null;
    }
}
