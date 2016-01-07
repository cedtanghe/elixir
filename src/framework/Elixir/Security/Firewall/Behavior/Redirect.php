<?php

namespace Elixir\Security\Firewall\Behavior;

use Elixir\HTTP\Request;
use Elixir\HTTP\RequestFactory;
use Elixir\HTTP\ResponseFactory;
use Elixir\HTTP\Session\SessionInterface;
use Elixir\Security\Firewall\FirewallInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Redirect
{
    /**
     * @var string 
     */
    protected $_message;
    
    /**
     * @var string
     */
    protected $_redirectURL;
    
    /**
     * @var Request
     */
    protected $_request;

    /**
     * @param string $pRedirectURL
     * @param string $pMessage
     * @param Request $pRequest
     */
    public function __construct($pRedirectURL = '/login', $pMessage = 'Please log in.', Request $pRequest = null) 
    {
        $this->_redirectURL = $pRedirectURL;
        $this->_message = $pMessage;
        $this->_request = $pRequest ?: RequestFactory::create();
    }
    
    /**
     * @return string
     */
    public function getRedirectURL()
    {
        return $this->_redirectURL;
    }
    
    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }
    
    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    /**
     * @param FirewallInterface $pFirewall
     */
    public function __invoke(FirewallInterface $pFirewall)
    {
        $this->_request->getSession()->flash(SessionInterface::FLASH_REDIRECT, $this->_request->getURL());
        $this->_request->getSession()->flash(SessionInterface::FLASH_INFOS, $this->_message);
        
        ResponseFactory::redirect($this->_redirectURL, 302, $this->_request->getServer('SERVER_PROTOCOL'));
    }
}
