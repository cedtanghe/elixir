<?php

namespace Elixir\Security;

use Elixir\HTTP\Request;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class CSRF
{
    /**
     * @var integer
     */
    const DEFAULT_TIME = 3600;
    
    /**
     * @var string
     */
    const TOKEN_KEY = '___CSRF___';
    
    /**
     * @var Request 
     */
    protected $_request;
    
    /**
     * @param Request $pRequest
     */
    public function __construct(Request $pRequest) 
    {
        $this->_request = $pRequest;
    }
    
    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    /**
     * @param string $pName
     * @param integer|string|\DateTime $pTime
     * @return string
     */
    public function create($pName, $pTime = self::DEFAULT_TIME)
    {
        if($pTime instanceof \DateTime)
        {
            $pTime = $pTime->format('U');
        }
        else if(!is_numeric($pTime))
        {
            $pTime = strtotime($pTime);
        }
        
        $token = uniqid(rand(), true);
        $this->_request->getSession()->set([self::TOKEN_KEY, $pName . $token], time() + $pTime);
        
        return $token;
    }
    
    public function invalidate()
    {
        $tokens = $this->_request->getSession()->get(self::TOKEN_KEY, []);
        $time = time();
            
        foreach($tokens as $key => $value)
        {
            if($time > $value)
            {
                unset($tokens[$key]);
            }
        }
        
        $this->_request->getSession()->set(self::TOKEN_KEY, $tokens);
    }
    
    /**
     * @param string $pName
     * @param string $pReferer
     * @return boolean
     */
    public function isValid($pName, $pReferer = null)
    {
        $error = false;
        $token = $this->_request->getPost($pName, null);
        
        if(null === $token)
        {
            $error = true;
        }
        
        if(!$error)
        {
            $name = $pName . $token;
            $time = $this->_request->getSession()->get([self::TOKEN_KEY, $name], null);
            
            if(null === $time)
            {
                $error = true;
            }
            
            if(!$error)
            {
                $this->_request->getSession()->remove([self::TOKEN_KEY, $name]);
                
                if(time() > $time)
                {
                    $error = true;
                }

                if(!$error)
                {
                    if(null !== $pReferer)
                    {
                        if($this->_request->getServer('HTTP_REFERER') != $pReferer)
                        {
                            $error = true;
                        }
                    }
                }
            }
        }
        
        $this->invalidate();
        return !$error;
    }
}
