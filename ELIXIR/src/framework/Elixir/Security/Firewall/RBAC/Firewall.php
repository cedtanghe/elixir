<?php

namespace Elixir\Security\Firewall\RBAC;

use Elixir\Security\Firewall\FirewallAbstract;
use Elixir\Security\Firewall\FirewallEvent;
use Elixir\Security\Firewall\Loader\LoaderFactory;
use Elixir\Security\Firewall\RBAC\AccessControl;
use Elixir\Security\RBAC\RBACInterface;

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
     * @throws \LogicException
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
                $roles = $options['roles'];
                $permissions = $options['permissions'];
                $assert = $options['assert'];
                
                if(count($domains) == 0)
                {
                    // Access granted
                    $this->dispatch(new FirewallEvent(FirewallEvent::ACCESS_GRANTED, $pResource, $accessControl));
                    return true;
                }
                
                $domainFound = false;
                
                foreach($domains as $domain)
                {
                    if($this->_authManager->has($domain))
                    {
                        if(count($roles) == 0)
                        {
                            // Access granted
                            $this->dispatch(new FirewallEvent(FirewallEvent::ACCESS_GRANTED, $pResource, $accessControl));
                            return true;
                        }
                        
                        $domainFound = true;
                        $identity = $this->_authManager->get($domain);
                        $context = $identity->getSecurityContext();
                        
                        if(!$context instanceof RBACInterface)
                        {
                            throw new \LogicException(sprintf('The security context of identity domain "%s" should be "Elixir\Security\RBAC\RBACInterface" type.', $domain));
                        }

                        if(count($permissions) == 0)
                        {
                            $permissions = [null];
                        }

                        foreach($roles as $role)
                        {
                            foreach($permissions as $permission)
                            {
                                if($context->isGranted($role, $permission, $assert))
                                {
                                    // Access granted
                                    $this->dispatch(new FirewallEvent(FirewallEvent::ACCESS_GRANTED, $pResource, $accessControl));
                                    return true;
                                }
                            }
                        }
                    }
                }
                
                if(!$domainFound)
                {
                    // Login required
                    $this->dispatch(new FirewallEvent(FirewallEvent::IDENTITY_NOT_FOUND, $pResource, $accessControl));
                    return false;
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
