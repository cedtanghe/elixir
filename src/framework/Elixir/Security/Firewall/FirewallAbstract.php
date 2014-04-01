<?php

namespace Elixir\Security\Firewall;

use Elixir\Dispatcher\Dispatcher;
use Elixir\Security\Authentification\Manager;
use Elixir\Security\Firewall\Writer\WriterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class FirewallAbstract extends Dispatcher implements FirewallInterface
{
    /**
     * @var integer
     */
    protected $_level = 0;
    
    /**
     * @var boolean
     */
    protected $_sorted = false;
    
    /**
     * @var integer
     */
    protected $_serial = 0;
    
    /**
     * @var Manager
     */
    protected $_authManager;
    
    /**
     * @var array
     */
    protected $_accessControls = array();
    
    /**
     * @param Manager $pAuthManager
     */
    public function __construct(Manager $pAuthManager)
    {
        $this->_authManager = $pAuthManager;
    }
    
    /**
     * @return Manager
     */
    public function getAuthManager()
    {
        return $this->_authManager;
    }
    
    /**
     * @param mixed $pConfig
     */
    abstract public function load($pConfig);
    
    /**
     * @param WriterInterface $pWriter
     * @param string $pFile
     * @return boolean
     */
    public function export(WriterInterface $pWriter, $pFile)
    {
        $pWriter->setFirewall($this);
        return $pWriter->export($pFile);
    }
    
    /**
     * @param AccessControlInterface $pAccessControl
     * @return boolean
     */
    public function hasAccessControl(AccessControlInterface $pAccessControl)
    {
        foreach($this->_accessControls as $value)
        {
            if($value['accessControl'] === $pAccessControl)
            {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @param AccessControlInterface $pAccessControl
     * @param integer $pPriority
     */
    public function addAccessControl(AccessControlInterface $pAccessControl, $pPriority = 0)
    {
        if(!$this->hasAccessControl($pAccessControl))
        {
            $this->_sorted = false;
            $this->_accessControls[] = array('accessControl' => $pAccessControl,
                                             'priority' =>$pPriority, 
                                             'serial' => $this->_serial++);
        }
    }
    
    /**
     * @param AccessControlInterface $pAccessControl
     */
    public function removeAccessControl(AccessControlInterface $pAccessControl)
    {
        $i = count($this->_accessControls);
        
        while($i--)
        {
            $data = $this->_accessControls[$i];
            
            if($data['accessControl'] === $pAccessControl)
            {
                array_splice($this->_accessControls, $i, 1);
                break;
            }
        }
    }
    
    /**
     * @see FirewallInterface::getAccessControls();
     */
    public function getAccessControls($pWithInfos = false)
    {
        $accessControls = array();
            
        foreach($this->_accessControls as $data)
        {
            $accessControls[] = $pWithInfos ? $data : $data['accessControl'];
        }

        return $accessControls;
    }
    
    /**
     * @param array $pData
     */
    public function setAccessControls(array $pData)
    {
        $this->_accessControls = array();
        $this->_serial = 0;
        
        foreach($pData as $data)
        {
            $accessControl = $data;
            $priority = 0;
            
            if(is_array($data))
            {
                $accessControl = $data['accessControl'];
                
                if(isset($data['priority']))
                {
                    $priority = $data['priority'];
                }
            }
            
            $this->addAccessControl($accessControl, $priority);
        }
    }
    
    /**
     * @internal
     */
    public function sort()
    {
        if(!$this->_sorted)
        {
            usort($this->_accessControls, array($this, 'compare'));
            $this->_sorted = true;
        }
    }
    
    /**
     * @param array $p1
     * @param array $p2
     * @return integer
     */
    protected function compare(array $p1, array $p2)
    {
        if ($p1['priority'] == $p2['priority']) 
        {
            return ($p1['serial'] < $p2['serial']) ? -1 : 1;
        }
        
        return ($p1['priority'] > $p2['priority']) ? -1 : 1;
    }
    
    /**
     * @param array $pData
     */
    public function merge($pData)
    {
        if($pData instanceof self)
        {
            $pData = $pData->getAccessControls(true);
        }
        
        if(count($pData) > 0)
        {
            $this->_sorted = false;
        
            foreach($pData as $ac)
            {
                $priority = 0;
                $serial = 0;
            
                if(is_array($ac))
                {
                    $ac = $ac['accessControl'];

                    if(isset($ac['priority']))
                    {
                        $priority = $ac['priority'];
                    }
                    
                    if(isset($ac['serial']))
                    {
                        $serial = $ac['serial'];
                    }
                }
                
                $this->_accessControls[] = array(
                    'accessControl' => $ac,
                    'priority' => $priority, 
                    'serial' => ($this->_serial++) + $serial
                );
            }
        }
    }
}