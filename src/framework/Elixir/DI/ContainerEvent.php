<?php

namespace Elixir\DI;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class ContainerEvent extends Event
{
    /**
     * @var string
     */
    const SERVICE_CREATED = 'service_created';
    
    /**
     * @var string
     */
    const SERVICE_ALIAS = 'service_alias';
    
    /**
     * @var string 
     */
    protected $_name;
    
    /**
     * @var string 
     */
    protected $_alias;
    
    /**
     * @var string 
     */
    protected $_serviceType;
    
    /**
     * @see Event::__contruct()
     * @param string $pName
     * @param string $pAlias
     * @param string $pServiceType
     */
    public function __construct($pType, $pName = null, $pAlias = null, $pServiceType = null) 
    {
        parent::__construct($pType);
        
        $this->_name = $pName;
        $this->_alias = $pAlias;
        $this->_serviceType = $pServiceType;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->_alias;
    }
    
    /**
     * @return string
     */
    public function getServiceType()
    {
        return $this->_serviceType;
    }
}