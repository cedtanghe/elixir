<?php

namespace Elixir\View;

use Elixir\Util\File;
use Elixir\View\DataAbstract;
use Elixir\View\GlobalInterface;
use Elixir\View\Storage\Str;
use Elixir\View\ViewInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Manager extends DataAbstract
{
    /**
     * @var array
     */
    protected $_engines = [];
    
    /**
     * @var ViewInterface 
     */
    protected $_defaultEngine;

    /**
     * @return ViewInterface
     */
    public function getDefaultEngine()
    {
        return $this->_defaultEngine;
    }
    
    /**
     * @see ViewInterface::getDefaultExtension()
     */
    public function getDefaultExtension()
    {
        if(null !== $this->_defaultEngine)
        {
            return $this->_defaultEngine->getDefaultExtension();
        }
        
        return null;
    }
    
    /**
     * @param string $pName
     * @param ViewInterface $pView
     * @param string $pExtension
     * @param boolean $pDefaultEngine
     */
    public function registerEngine($pName, ViewInterface $pView, $pExtension = null, $pDefaultEngine = true)
    {
        $this->_engines[$pName] = [
            'extension' => $pExtension ?: $pView->getDefaultExtension(), 
            'view' => $pView
        ];
        
        if($pDefaultEngine)
        {
            $this->_defaultEngine = $pView;
        }
    }
    
    /**
     * @param $pName $pName
     * @param mixed $pDefault
     * @return mixed
     */
    public function getEngine($pName, $pDefault = null)
    {
        if(isset($this->_engines[$pName]))
        {
            return $this->_engines[$pName]['view'];
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @param string $pExtension
     * @param mixed $pDefault
     * @return mixed
     */
    public function getEngineByExtension($pExtension, $pDefault = null)
    {
        foreach($this->_engines as $key => $value)
        {
            if(preg_match('/' . $value['extension'] . '/i', $pExtension))
            {
                return $value['view'];
            }
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @return array
     */
    public function getEngines()
    {
        $engines = [];
        
        foreach($this->_engines as $key => $value)
        {
            $engines[$key] = $value['view'];
        }
        
        return $engines;
    }
    
    /**
     * @see ViewInterface::render()
     * @throws \LogicException
     */
    public function render($pTemplate, array $pData = [])
    {
        if(!($pTemplate instanceof Str))
        {
            $extension = File::extension($pTemplate);
            $view = $this->getEngineByExtension($extension, null);

            if(null === $view)
            {
                throw new \LogicException(sprintf('No view engine for "%s" extension.', $extension));
            }
        }
        else
        {
            $view = $this->getDefaultEngine();
        }
        
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