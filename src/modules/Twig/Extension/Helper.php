<?php

namespace Elixir\Module\Twig\Extension;

use Elixir\Helper\HelperInterface;
use Elixir\Module\Twig\View\Twig;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Helper implements \ArrayAccess
{
     /**
     * @var Twig
     */
    protected $_view;
    
    /**
     * @param Twig $pView
     */
    public function __construct(Twig $pView)
    {
        $this->_view = $pView;
    }
    
    /**
     * @see Config::has()
     */
    public function offsetExists($pKey) 
    { 
        return isset($this->$pKey);
    } 

    /**
     * @param mixed $pKey
     * @param mixed $pValue
     * @throws \LogicException
     */
    public function offsetSet($pKey, $pValue) 
    { 
        throw new \LogicException('You can not execute this method in this context.');
    } 

    /**
     * @see Helper::__get()
     */
    public function offsetGet($pKey) 
    { 
        return $this->$pKey;
    } 

    /**
     * @param mixed $pKey
     * @throws \LogicException
     */
    public function offsetUnset($pKey) 
    { 
        throw new \LogicException('You can not execute this method in this context.');
    } 
    
    /**
     * @param string $pName
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __get($pName)
    {
        $helper = $this->_view->getHelperContainer()->get('helper.' . $pName);
        
        if(null === $helper)
        {
            throw new \InvalidArgumentException(sprintf('Helper "%s" is not defined.', $pName));
        }
        
        return $helper;
    }
    
    /**
     * @param string $pName
     * @return boolean
     */
    public function __isset($pName)
    {
        return null !== $this->_view->getHelperContainer()->has('helper.' . $pName);
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($pMethod, $pArguments)
    {
        $helper = $this->_view->getHelperContainer()->get('helper.' . $pMethod);
        
        if(null !== $helper)
        {
            if(is_callable($pOptions))
            {
                return call_user_func_array($helper, $pArguments);
            }
            else
            {
                $method = $helper instanceof HelperInterface ? 'direct' : 'filter';
                return call_user_func_array(array($helper, $method), $pArguments);
            }
        }
        
        throw new \BadMethodCallException(sprintf('Helper "%s" is not defined.', $pMethod));
    }
}