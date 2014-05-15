<?php

namespace Elixir\Routing;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Route
{
    /**
     * @var string
     */
    const MVC = '_mvc';
    
    /**
     * @var string
     */
    const MODULE = '_module';
    
    /**
     * @var string
     */
    const CONTROLLER = '_controller';
    
    /**
     * @var string
     */
    const ACTION = '_action';
    
    /**
     * @var string
     */
    const SECURE = '_secure';
    
    /**
     * @var string
     */
    const METHOD = '_method';
    
    /**
     * @var string
     */
    const ASSERT = '_assert';
    
    /**
     * @var string
     */
    const GENERATE_FILTER = '_generate_filter';
    
    /**
     * @var string
     */
    const MATCHED_FILTER = '_matched_filter';
    
    /**
     * @var string
     */
    const PREFIX = '_prefix';
    
    /**
     * @var string
     */
    const SUFFIX = '_suffix';
    
    /**
     * @var string
     */
    const REPLACEMENTS = '_replacements';
    
    /**
     * @var string
     */
    const REPLACEMENTS_ALIAS = '%';
    
    /**
     * @var string
     */
    const ATTRIBUTES = '_attributes';
    
    /**
     * @var string
     */
    const ATTRIBUTES_ALIAS = '*';
    
    /**
     * @var string
     */
    const QUERY = '_query';
    
    /**
     * @var string
     */
    const QUERY_ALIAS = '?';
    
    /**
     * @var string
     */
    const MASK_WORD = '[a-zA-Z0-9\-_ ]+';
    
    /**
     * @var string
     */
    const MASK_INTEGER = '[0-9]+';
    
    /**
     * @var string
     */
    protected $_pattern;
    
    /**
     * @var array
     */
    protected $_parameters;
    
    /**
     * @var array
     */
    protected $_options;

    /**
     * @param string $pPattern
     * @param array $pParameters
     * @param array $pOptions
     */
    public function __construct($pPattern, array $pParameters = [], array $pOptions = [])
    {
        $this->_pattern = trim($pPattern, '/');
        $this->setParameters($pParameters);
        $this->setOptions($pOptions);
    }
    
    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->_pattern;
    }
    
    /**
     * @param string $pValue
     * @throws \InvalidArgumentException
     */
    public function mvc($pValue)
    {
        $mvc = explode('::', $pValue);
                
        if(count($mvc) != 3)
        {
            throw new \InvalidArgumentException(sprintf('Parameter "%s" is not valid.', self::MVC));
        }

        $this->setModule($mvc[0]);
        $this->setController($mvc[1]);
        $this->setAction($mvc[2]);
    }
    
    /**
     * @param string $pValue
     */
    public function setModule($pValue)
    {
        $this->_parameters[self::MODULE] = $pValue;
    }
    
    /**
     * @return string
     */
    public function getModule()
    {
        return $this->getParameter(self::MODULE);
    }

    /**
     * @param string $pValue
     */
    public function setController($pValue)
    {
        $this->_parameters[self::CONTROLLER] = $pValue;
    }
    
    /**
     * @return string
     */
    public function getController()
    {
        return $this->getParameter(self::CONTROLLER);
    }
    
    /**
     * @param string $pValue
     */
    public function setAction($pValue)
    {
        $this->_parameters[self::ACTION] = $pValue;
    }
    
    /**
     * @return string
     */
    public function getAction()
    {
        return $this->getParameter(self::ACTION);
    }
    
    /**
     * @param string $pKey
     * @return boolean
     */
    public function hasParameter($pKey)
    {
        return array_key_exists($pKey, $this->_parameters);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function getParameter($pKey, $pDefault = null)
    {
        if($this->hasParameter($pKey))
        {
            return $this->_parameters[$pKey];
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @param string $pKey
     * @param mixed $pValue
     */
    public function setParameter($pKey, $pValue)
    {
        switch($pKey)
        {
            case self::MVC:
                $this->mvc($pValue);
            break;
            case self::QUERY:
            case self::QUERY_ALIAS:
                if(!is_array($pValue))
                {
                    parse_str($pValue, $result);
                    $pValue = $result;
                }
                
                $this->_parameters[self::QUERY] = $pValue;
            break;
            default:
                $this->_parameters[$pKey] = $pValue;
            break;
        }
    }
    
    /**
     * @param string $pKey
     */
    public function removeParameter($pKey)
    {
        unset($this->_parameters[$pKey]);
    }
    
    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }
    
    /**
     * @param array $pData
     */
    public function setParameters(array $pData)
    {
        $this->_parameters = [];
        
        foreach($pData as $key => $value)
        {
            $this->setParameter($key, $value);
        }
    }
    
    /**
     * @param string $pValue
     */
    public function prefix($pValue)
    {
        if(substr($this->_pattern, 0, strlen($pValue)) != $pValue)
        {
            $this->_pattern = $pValue . $this->_pattern;
            $this->_pattern = trim($this->_pattern, '/');
        }
    }
    
    /**
     * @param string $pValue
     */
    public function suffix($pValue)
    {
        if(substr($this->_pattern, -strlen($pValue)) != $pValue)
        {
            $this->_pattern .= $pValue;
            $this->_pattern = trim($this->_pattern, '/');
        }
    }

    /**
     * @param boolean $pValue
     */
    public function setSecure($pValue)
    {
        $this->_options[self::SECURE] = $pValue;
    }
    
    /**
     * @return boolean
     */
    public function isSecure()
    {
        return $this->getOption(self::SECURE, false);
    }
    
    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->getOption(self::METHOD, []);
    }
    
    /**
     * @param array $pData
     * @throws \InvalidArgumentException
     */
    public function setMethods(array $pData)
    {
        $methods = ['POST', 'GET', 'PUT', 'DELETE'];
        
        foreach($pData as &$method)
        {
            $method = strtoupper($method);

            if(!in_array($method, $methods))
            {
                throw new \InvalidArgumentException(sprintf('Options parameter "%s" is not valid.', self::METHOD));
            }
        }

        $this->_options[self::METHOD] = $pData;
    }
    
    /**
     * @param boolean $pValue
     */
    public function setUseAttributes($pValue)
    {
        if($pValue)
        {
            $this->_options[self::ATTRIBUTES] = '(/.+)?';
        }
        else
        {
            unset($this->_options[self::ATTRIBUTES]);
        }
    }
    
    /**
     * @return boolean
     */
    public function isUseAttributes()
    {
        return $this->getOption(self::ATTRIBUTES, false);
    }
    
    /**
     * @return array
     */
    public function getReplacements()
    {
        $this->_options[self::REPLACEMENTS] = $pData;
    }
    
    /**
     * @param array $pData
     */
    public function setReplacements(array $pData)
    {
        $this->_options[self::REPLACEMENTS] = $pData;
    }
    
    /**
     * @param callable $pValue
     * @throws \InvalidArgumentException
     */
    public function setAssert($pValue)
    {
        if(!is_callable($pValue))
        {
            throw new \InvalidArgumentException('Assert method must be a callable.');
        }
        
        $this->_options[self::ASSERT] = $pValue;
    }
    
    /**
     * @return callable
     */
    public function  getAssert()
    {
        return $this->getOption(self::ASSERT);
    }
    
    /**
     * @param callable $pValue
     * @throws \InvalidArgumentException
     */
    public function setGenerateFilter($pValue)
    {
        if(!is_callable($pValue))
        {
            throw new \InvalidArgumentException('Generate filter method must be a callable.');
        }
        
        $this->_options[self::GENERATE_FILTER] = $pValue;
    }
    
    /**
     * @return callable
     */
    public function getGenerateFilter()
    {
        return $this->getOption(self::GENERATE_FILTER);
    }
    
    /**
     * @param callable $pValue
     * @throws \InvalidArgumentException
     */
    public function setMatchedFilter($pValue)
    {
        if(!is_callable($pValue))
        {
            throw new \InvalidArgumentException('Matched filter method must be a callable.');
        }
        
        $this->_options[self::MATCHED_FILTER] = $pValue;
    }
    
    /**
     * @return callable
     */
    public function getMatchedFilter()
    {
        return $this->getOption(self::MATCHED_FILTER);
    }
    
    /**
     * @param string $pKey
     * @return boolean
     */
    public function hasOption($pKey)
    {
        return array_key_exists($pKey, $this->_options);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function getOption($pKey, $pDefault = null)
    {
        if($this->hasOption($pKey))
        {
            return $this->_options[$pKey];
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @param string $pKey
     * @param mixed $pValue
     */
    public function setOption($pKey, $pValue)
    {
        switch($pKey)
        {
            case self::SECURE:
                $this->setSecure($pValue);
            break;
            case self::METHOD:
                $this->setMethods((array)$pValue);
            break;
            case self::ATTRIBUTES:
            case self::ATTRIBUTES_ALIAS:
                $this->setUseAttributes($pValue);
            break;
            case self::REPLACEMENTS:
            case self::REPLACEMENTS_ALIAS:
                $this->setReplacements($pValue);
            break;
            case self::ASSERT:
                $this->setAssert($pValue);
            break;
            case self::GENERATE_FILTER:
                $this->setGenerateFilter($pValue);
            break;
            case self::MATCHED_FILTER:
                $this->setMatchedFilter($pValue);
            break;
            case self::PREFIX:
                $this->prefix($pValue);
            break;
            case self::SUFFIX:
                $this->suffix($pValue);
            break;
            default:
                $this->_options[$pKey] = $pValue;
            break;
        }
    }
    
    /**
     * @param string $pKey
     */
    public function removeOption($pKey)
    {
        unset($this->_options[$pKey]);
    }
    
    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
    /**
     * @param array $pData
     */
    public function setOptions(array $pData)
    {
        $this->_options = [];
        
        foreach($pData as $key => $value)
        {
            $this->setOption($key, $value);
        }
    }
}
