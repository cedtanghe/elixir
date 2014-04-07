<?php

namespace Elixir\View\PHP;

use Elixir\Helper\HelperInterface;
use Elixir\View\Storage\StorageInterface;
use Elixir\View\Storage\Str;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Parser
{
    /**
     * @var PHP
     */
    protected $__view;
    
    /**
     * @param PHP $pView
     */
    public function __construct(PHP $pView) 
    {
        $this->__view = $pView;
    }
    
    /**
     * @param string|StorageInterface $pTemplate
     * @return string
     * @throws \UnexpectedValueException
     * @throws \Exception
     */
    public function parse($pTemplate)
    {
        $__template = $pTemplate;
        unset($pTemplate);
        
        ob_start();
        
        try
        {
            if($__template instanceof Str)
            {
                eval('; ?>' . $__template->getContent() . '<?php ;');
            }
            else
            {
                $include = include $__template;
                
                if(false === $include)
                {
                    throw new \UnexpectedValueException(sprintf('File "%s" include failed.', $__template));
                }
            }
            
            $content = ob_get_clean();
        }
        catch(\Exception $e)
        {
            ob_end_clean();
            throw $e;
        }
        
        return $content;
    }
    
    /**
     * @return array
     */
    public function data()
    {
        return $this->__view->gets();
    }

    /**
     * @see PHP::has()
     */
    public function __isset($pKey) 
    {
        return $this->__view->has($pKey);
    }
    
    /**
     * @see PHP::get()
     */
    public function __get($pKey)
    {
        return $this->__view->get($pKey);
    }

    /**
     * @see PHP::set()
     */
    public function __set($pKey, $pValue)
    {
        $this->__view->set($pKey, $pValue);
    }
    
    /**
     * @see PHP::remove()
     */
    public function __unset($pKey) 
    {
        $this->__view->remove($pKey);
    }

    /**
     * @param string $pMethod
     * @param array $pArguments
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($pMethod, $pArguments)
    {
        if(in_array($pMethod, array('helper', 
                                    'extend', 
                                    'start', 
                                    'parent', 
                                    'end', 
                                    'block', 
                                    'escape', 
                                    'raw')))
        {
            return call_user_func_array(array($this->__view, $pMethod), $pArguments);
        }
        
        $helper = $this->__view->helper('helper.' . $pMethod);
        
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
