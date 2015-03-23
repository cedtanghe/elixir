<?php

namespace Elixir\Security\Firewall\Identity;

use Elixir\Security\Firewall\FirewallAbstract;
use Elixir\Security\Firewall\FirewallEvent;
use Elixir\Security\Firewall\Identity\AccessControl;
use Elixir\Security\Firewall\Loader\LoaderFactory;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Firewall extends FirewallAbstract
{
    /**
     * @see FirewallAbstract::load()
     */
    public function load($pConfig)
    {
        if($pConfig instanceof self)
        {
            $this->merge($pConfig);
        }
        else
        {
            foreach((array)$pConfig as $config)
            {
                $loader = LoaderFactory::create($config);
                $data = $loader->load($config);
                
                foreach($data as $ac)
                {
                    $this->addAccessControl(new AccessControl($ac['regex'], $ac['options']), $ac['priority']);
                }
            }
        }
    }
    
    /**
     * @see FirewallAbstract::analyze()
     */
    public function analyze($pResource)
    {
        $this->sort();
        $pResource = trim($pResource, '/');
        
        $this->dispatch(new FirewallEvent(FirewallEvent::ANALYSE, $pResource));
        
        foreach($this->_accessControls as $data)
        {
            $accessControl = $data['accessControl'];
            
            if(preg_match($accessControl->getPattern(), $pResource))
            {
                $this->dispatch(new FirewallEvent(FirewallEvent::RESOURCE_MATCHED, $pResource, $accessControl));
                
                $options = $accessControl->getOptions();
                $domains = $options['domains'];
                
                if(count($domains) == 0)
                {
                    // Access granted
                    $this->dispatch(new FirewallEvent(FirewallEvent::ACCESS_GRANTED, $pResource, $accessControl));
                    return true;
                }
                else if($this->_authManager->isEmpty())
                {
                    // Login required
                    $this->dispatch(new FirewallEvent(FirewallEvent::IDENTITY_NOT_FOUND, $pResource, $accessControl));
                    return false;
                }
                
                foreach($domains as $domain)
                {
                    if($this->_authManager->has($domain))
                    {
                        // Access granted
                        $this->dispatch(new FirewallEvent(FirewallEvent::ACCESS_GRANTED, $pResource, $accessControl));
                        return true;
                    }
                }
                
                // Forbidden
                $this->dispatch(new FirewallEvent(FirewallEvent::ACCESS_FORBIDDEN, $pResource, $accessControl));
                return false;
            }
        }
        
        // No access controls found
        $this->dispatch(new FirewallEvent(FirewallEvent::NO_ACCESS_CONTROLS_FOUND, $pResource));
        return true;
    }
}
