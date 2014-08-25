<?php

namespace Elixir\Validator;

use Elixir\HTTP\RequestFactory;
use Elixir\Security\CSRF as Context;
use Elixir\Validator\ValidatorAbstract;
use Elixir\Validator\ValidatorInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class CSRF extends ValidatorAbstract
{
    /**
     * @var array 
     */
    protected $_errorMessageTemplates = [self::ERROR => 'Possibility of attack CSRF flaw.'];
    
    /**
     * @var Context
     */
    protected $_CSRF;
    
    /**
     * @param Context $pCSRF
     */
    public function __construct(Context $pCSRF = null) 
    {
        $this->_CSRF = $pCSRF;
    }
    
    /**
     * @param Context $pValue
     */
    public function setCSRF(Context $pValue)
    {
        $this->_CSRF = $pValue;
    }
    
    /**
     * @return Context
     */
    public function getCSRF()
    {
        if(null === $this->_CSRF)
        {
            $this->_CSRF = new Context(RequestFactory::create());
        }
        
        return $this->_CSRF;
    }
    
    /**
     * @param string $pName
     * @param integer|string|\DateTime $pTime
     * @return integer
     */
    public function createToken($pName, $pTime = Context::DEFAULT_TIME)
    {
        $token = $this->getCSRF()->create($pName, $pTime);
        return $token;
    }

    /**
     * @see ValidatorInterface::isValid()
     */
    public function isValid($pContent, array $pOptions = []) 
    {
        $pOptions = array_merge($this->_options, $pOptions);
        $this->_errors = [];
        
        if(!$this->getCSRF()->isValid($pContent, isset($pOptions['referer']) ? $pOptions['referer'] : null))
        {
            $this->_errors[] = $this->getErrorMessageTemplate(self::ERROR);
        }
        
        if($this->hasError())
        {
            if(isset($pOptions['error']))
            {
                $this->_errors = (array)$pOptions['error'];
            }
            
            return false;
        }
        
        return true;
    }
}
