<?php

namespace Elixir\Module\Twig\Extension;

use Elixir\Helper\HelperInterface;
use Elixir\Module\Twig\View\Twig;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Helper
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
        $this->_view = $pEnvironment;
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
            if($helper instanceof \Closure)
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