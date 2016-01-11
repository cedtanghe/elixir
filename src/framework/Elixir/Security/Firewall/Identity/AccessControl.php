<?php

namespace Elixir\Security\Firewall\Identity;

use Elixir\Security\Firewall\AccessControlAbstract;
use Elixir\Security\Firewall\AccessControlInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class AccessControl extends AccessControlAbstract
{
    /**
     * @var string
     */
    protected $_pattern;
    
    /**
     * @var array
     */
    protected $_options = [
        'assert' => null,
        'domains' => []
    ];
    
    /**
     * @param string $pPattern
     * @param array $pOptions
     */
    public function __construct($pPattern, array $pOptions = [])
    {
        $this->_pattern = $pPattern;
        
        foreach($pOptions as $key => $value)
        {
            $this->setOption($key, $value);
        }
    }
    
    /**
     * @see AccessControlInterface::getPattern()
     */
    public function getPattern()
    {
        return $this->_pattern;
    }
    
    /**
     * @see AccessControlAbstract::setOption()
     */
    public function setOption($pKey, $pValue)
    {
        if($pKey === 'assert')
        {
            $this->setAssert($pValue);
        }
        else if($pKey === 'domains')
        {
            if(null === $pValue)
            {
                $pValue = [];
            }
            
            $this->setDomains((array)$pValue);
        }
        else
        {
            parent::setOption($pKey, $pValue);
        }
    }
    
    /**
     * @param callable $pValue
     */
    public function setAssert(callable $pValue)
    {
        $this->_options['assert'] = $pValue;
    }
    
    /**
     * @return callable
     */
    public function getAssert()
    {
        return $this->_options['assert'];
    }
    
    /**
     * @param string $pDomain
     */
    public function addDomain($pDomain)
    {
        if(!in_array($pDomain, $this->_options['domains']))
        {
            $this->_options['domains'][] = $pDomain;
        }
    }
    
    /**
     * @return array
     */
    public function getDomains()
    {
        return $this->_options['domains'];
    }
    
    /**
     * @param array $pData
     */
    public function setDomains(array $pData)
    {
        $this->_options['domains'] = [];
        
        foreach($pData as $domain)
        {
            $this->addDomain($domain);
        }
    }
}
