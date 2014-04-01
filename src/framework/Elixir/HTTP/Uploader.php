<?php

namespace Elixir\HTTP;

use Elixir\Filter\FilterInterface;
use Elixir\Validator\ValidatorInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Uploader
{
    /**
     * @var string
     */
    const UNKNOWN_ERROR = 'unknown_error';
    
    /**
     * @var string
     */
    const INI_SIZE_ERROR = 'ini_size_error';
    
    /**
     * @var string
     */
    const FORM_SIZE_ERROR = 'size_error';
    
    /**
     * @var string
     */
    const PARTIAL_ERROR = 'partial_error';
    
    /**
     * @var string
     */
    const NO_FILE_ERROR = 'no_file_error';
    
    /**
     * @var string
     */
    protected $_name;
    
    /**
     * @var string|array
     */
    protected $_fileName;
    
    /**
     * @var boolean
     */
    protected $_receive;
    
    /**
     * @var string
     */
    protected $_errorMessage;
    
    /**
     * @var array
     */
    protected $_filters = array();
    
    /**
     * @var array
     */
    protected $_validators = array();
    
    /**
     * @var array
     */
    protected $_errors = array();
    
    /**
     * @var boolean
     */
    protected $_errorBreak = true;
    
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = array(self::UNKNOWN_ERROR => 'An error occurred during upload.');
    
    /**
     * @var array
     */
    protected $_fileInfos;
    
    /**
     * @param string $pName
     */
    public function __construct($pName = null) 
    {
        if(null !== $pName)
        {
            $this->_name = $pName;
        }
    }
    
    /**
     * @param string
     */
    public function setName($pValue)
    {
        $this->_name = $pValue;
        $this->reset();
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * @return string|array
     */
    public function getFileName()
    {
        if(null === $this->_fileName)
        {
            $result = array();
            $filesInfos = $this->getFileInfo();

            if(null === $filesInfos)
            {
                return null;
            }

            foreach($filesInfos as $file)
            {
                $result[] = $file['name'];
            }

            $this->_fileName = count($result) > 1 ? $result : $result[0];
        }
        
        return $this->_fileName;
    }
    
    /**
     * @return string|array
     */
    public function getTempName()
    {
        $result = array();
        $filesInfos = $this->getFileInfo();
            
        if(null === $filesInfos)
        {
            return null;
        }

        foreach($filesInfos as $file)
        {
            $result[] = $file['tmp_name'];
        }
        
        return count($result) > 1 ? $result : $result[0];
    }
    
    /**
     * @return boolean
     */
    public function hasMultipleFiles()
    {
        return is_array($this->getFileName());
    }
    
    /**
     * @param string $pValue
     */
    public function setErrorMessage($pValue)
    {
        $this->_errorMessage = $pValue;
    }
    
    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }
    
    /**
     * @param string $pKey
     * @param string $pValue
     */
    public function setErrorMessageTemplate($pKey, $pValue)
    {
        $this->_errorMessageTemplates[$pKey] = $pValue;
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function getErrorMessageTemplate($pKey, $pDefault = null)
    {
        if(isset($this->_errorMessageTemplates[$pKey]))
        {
            return $this->_errorMessageTemplates[$pKey];
        }
        
        if(is_callable($pDefault))
        {
            return call_user_func($pDefault);
        }
        
        return $pDefault;
    }
    
    /**
     * @return array
     */
    public function getErrorMessageTemplates()
    {
        return $this->_errorMessageTemplates;
    }
    
    /**
     * @param array $pData
     */
    public function setErrorMessageTemplates(array $pData)
    {
        $this->_errorMessageTemplates = array();
        
        foreach($pData as $key => $value)
        {
            $this->setErrorMessageTemplate($key, $value);
        }
    }
    
    /**
     * @param boolean $pValue
     */
    public function setErrorBreak($pValue)
    {
        $this->_errorBreak = $pValue;
    }
    
    /**
     * @return boolean
     */
    public function isErrorBreak()
    {
        return $this->_errorBreak;
    }
    
    /**
     * @param ValidatorInterface $pValidator
     * @param array $pOptions
     */
    public function addValidator(ValidatorInterface $pValidator, array $pOptions = array())
    {
        $this->_validators[] = array('validator' => $pValidator, 'options' => $pOptions);
    }
    
    /**
     * @return array
     */
    public function getValidators()
    {
        return $this->_validators;
    }
    
    /**
     * @param array $pData
     */
    public function setValidators(array $pData)
    {
        $this->_validators = array();
        
        foreach($pData as $data)
        {
            $validator = $data;
            $options = array();
            
            if(is_array($data))
            {
                $validator = $data['validator'];
                
                if(isset($data['options']))
                {
                    $options = $data['options'];
                }
            }
            
            $this->addValidator($validator, $options);
        }
    }
    
    /**
     * @param FilterInterface $pFilter
     * @param array $pOptions
     */
    public function addFilter(FilterInterface $pFilter, array $pOptions = array())
    {
        $this->_filters[] = array('filter' => $pFilter, 'options' => $pOptions);
    }
    
    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->_filters;
    }
    
    /**
     * @param array $pData
     */
    public function setFilters(array $pData)
    {
        $this->_filters = array();
        
        foreach($pData as $data)
        {
            $filter = $data;
            $options = array();
            
            if(is_array($data))
            {
                $filter = $data['filter'];
                
                if(isset($data['options']))
                {
                    $options = $data['options'];
                }
            }
            
            $this->addFilter($filter, $options);
        }
    }
    
    /**
     * @param array $pFile
     * @return array
     */
    public function getFileInfo()
    {
        if(isset($_FILES[$this->_name]))
        {
            $this->_fileInfos = array();
            
            $file = $_FILES[$this->_name];
            $len = count((array)$file['name']);
            $keys = array_keys($file);
            
            for($i = 0; $i < $len ; ++$i)
            {
                foreach($keys as $key) 
                {
                    $d = (array)$file[$key];
                    $this->_fileInfos[$i][$key] = $d[$i];
                }
            }
        }
        
        return $this->_fileInfos;
    }
    
    /**
     * @return boolean
     */
    public function isUploaded()
    {
        $filesInfos = $this->getFileInfo();
            
        if(null === $filesInfos)
        {
            return false;
        }

        foreach($filesInfos as $file)
        {
            if(!is_uploaded_file($file['tmp_name']))
            {
                return false;
            }
        }

        return true;
    }
    
    /**
     * @return boolean
     */
    public function isValid()
    {
        $this->_errors = array();
        $filesInfos = $this->getFileInfo();
        $unknowError = $this->getErrorMessageTemplate(self::UNKNOWN_ERROR);
        
        if(null !== $filesInfos)
        {
            foreach($filesInfos as $file)
            {
                switch($file['error'])
                {
                    case UPLOAD_ERR_OK:
                        if(is_uploaded_file($file['tmp_name']))
                        {
                            foreach($this->_validators as $validator)
                            {
                                if(!$validator['validator']->isValid($file, $validator['options']))
                                {
                                    $this->_errors = array_merge($this->_errors, (array)$validator['validator']->errors());
                                
                                    if($this->_errorBreak)
                                    {
                                        break;
                                    }
                                }
                            }
                        }
                        else
                        {
                            $this->_errors[] = $unknowError;
                        }
                    break;
                    case UPLOAD_ERR_INI_SIZE:
                        $this->_errors[] = $this->getErrorMessageTemplate(self::INI_SIZE_ERROR, $unknowError);
                    break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $this->_errors[] = $this->getErrorMessageTemplate(self::FORM_SIZE_ERROR, $unknowError);
                    break;
                    case UPLOAD_ERR_PARTIAL:
                        $this->_errors[] = $this->getErrorMessageTemplate(self::PARTIAL_ERROR, $unknowError);
                    break;
                    case UPLOAD_ERR_NO_FILE:
                        $this->_errors[] = $this->getErrorMessageTemplate(self::NO_FILE_ERROR, $unknowError);
                    break;
                    default:
                        $this->_errors[] = $unknowError;
                    break;
                }
            }
            
            array_unique($this->_errors);
        }
        else
        {
            $this->_errors[] = $this->getErrorMessageTemplate(self::NO_FILE_ERROR, $unknowError);
        }
        
        return !$this->hasError();
    }
    
    /**
     * @return boolean
     */
    public function receive()
    {
        if($this->_receive)
        {
            return true;
        }
        
        $filesInfos = $this->getFileInfo();
            
        if(null !== $filesInfos)
        {
            $moved = array();
            
            foreach($filesInfos as $file)
            {
                foreach($this->_filters as $filter)
                {
                    $file = $filter['filter']->filter($file, $filter['options']);
                }
                
                if(move_uploaded_file($file['tmp_name'], $file['name']))
                {
                    $moved[] = $file['name'];
                }
                else
                {
                    foreach($moved as $f)
                    {
                        @unlink($f);
                    }
                    
                    return false;
                }
            }
            
            $this->_fileName = count($moved) == 1 ? $moved[0] : $moved;
            $this->_receive = true;
            
            return true;
        }

        return false;
    }
    
    /**
     * @return boolean
     */
    public function hasError()
    {
        return count($this->_errors) > 0;
    }
    
    /**
     * @return string|array
     */
    public function errors()
    {
        if(!$this->hasError())
        {
            return null;
        }
        
        if(null !== $this->_errorMessage)
        {
            return $this->_errorMessage;
        }
        
        return count($this->_errors) == 1 ? $this->_errors[0] : $this->_errors;
    }
    
    public function reset()
    {
        $this->_errors = array();
        $this->_fileInfos = null;
        $this->_fileName = null;
        $this->_receive = false;
    }
}