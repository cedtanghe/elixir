<?php

namespace Elixir\View;

use Elixir\DI\ContainerInterface;
use Elixir\Util\File;
use Elixir\View\Helper\Container;
use Elixir\View\Storage\Str;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Manager extends DataAbstract implements HelperInterface
{
    /**
     * @var string
     */
    const DEFAULT_EXTENSION = 'phtml';
    
    /**
     * @var string
     */
    protected $_defaultExtension = self::DEFAULT_EXTENSION;

    /**
     * @var Container|ContainerInterface
     */
    protected $_helper;
    
    /**
     * @var array
     */
    protected $_engines = array();
    
    /**
     * @param string $pValue
     */
    public function setDefaultExtension($pValue)
    {
        $this->_defaultExtension = $pValue;
    }
    
    /**
     * @return string
     */
    public function getDefaultExtension()
    {
        return $this->_defaultExtension;
    }
    
    /**
     * @param string $pExtension
     * @param ViewInterface $pView
     */
    public function registerExtension($pExt, ViewInterface $pView)
    {
        $this->_engines[$pExt] = $pView;
    }
    
    /**
     * @param string $pExtension
     */
    public function unregisterExtension($pExt)
    {
        unset($this->_engines[$pExt]);
    }
    
    /**
     * @see HelperInterface::setHelperContainer()
     */
    public function setHelperContainer($pValue)
    {
        $this->_helper = $pValue instanceof Container ? $pValue : new Container($pValue);
    }
    
    /**
     * @see HelperInterface::getHelperContainer()
     */
    public function getHelperContainer()
    {
        return $this->_helper;
    }
    
    /**
     * @see HelperInterface::helper()
     * @throws \LogicException
     */
    public function helper($pKey)
    {
        throw new \LogicException('You can not use view helper via a manager.');
    }
    
    /**
     * @param string $pExtension
     * @return ViewInterface
     * @throws \LogicException
     */
    public function getViewByExtension($pExt)
    {
        foreach($this->_engines as $key => $value)
        {
            if(preg_match('/' . $key . '/i', $pExt))
            {
                if(null !== $this->_helper)
                {
                    if($value instanceof HelperInterface)
                    {
                        $value->setHelperContainer($this->_helper);
                    }
                }
                
                return $value;
            }
        }
        
        throw new \LogicException(sprintf('No view engine for "%s" extension.', $pExt));
    }
    
    /**
     * @see ViewInterface::render()
     */
    public function render($pTemplate, array $pData = array())
    {
        $extension = !($pTemplate instanceof Str) ? File::extension($pTemplate) : $this->getDefaultExtension();
        $view = $this->getViewByExtension($extension);
        $view->sets(array_merge($this->gets(), $pData));
        
        if($view instanceof GlobalInterface)
        {
            foreach($this->_global as $key => $value)
            {
                $view->globalize($key);
            }
        }
        
        return $view->render($pTemplate);
    }
}
