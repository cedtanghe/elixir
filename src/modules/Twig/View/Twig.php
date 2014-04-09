<?php

namespace Elixir\Module\Twig\View;

use Elixir\View\DataAbstract;
use Elixir\View\Helper\Container;
use Elixir\View\HelperInterface;
use Elixir\View\Storage\File;
use Elixir\View\Storage\Str;
use Elixir\View\ViewInterface;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_Loader_String;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Twig extends DataAbstract implements HelperInterface
{
    /**
     * @var Twig_Environment 
     */
    protected $_environment;
    
    /**
     * @var Twig_Loader_Filesystem
     */
    protected $_loaderFilesystem;
    
    /**
     * @var Twig_Loader_String
     */
    protected $_loaderString;
    
    /**
     * @var Container
     */
    protected $_helper;
    
    /**
     * @var array
     */
    protected $_paths;

    /**
     * @param array $pConfig
     * @param array $pPaths
     */
    public function __construct(array $pConfig = array(), array $pPaths = array())
    {
        $this->_environment = new Twig_Environment(
            null, 
            array_merge(
                array(
                    'cache' => APPLICATION_PATH . '/cache/twig',
                    'debug' => APPLICATION_ENV != 'production',
                    'strict_variables' => true
                ),
                $pConfig
            )
        );
        
        $pPaths[] = APPLICATION_PATH;
        $this->_paths = array_unique($pPaths);
    }
    
    /**
     * @return Twig_Environment
     */
    public function getEnvironment()
    {
        return $this->_environment;
    }
    
    /**
     * @return Twig_Loader_Filesystem
     */
    public function getLoaderFilesystem()
    {
        if(null === $this->_loaderFilesystem)
        {
            $this->_loaderFilesystem = new Twig_Loader_Filesystem($this->_paths);
        }
        
        return $this->_loaderFilesystem;
    }
    
    /**
     * @return Twig_Loader_String
     */
    public function getLoaderString()
    {
        if(null === $this->_loaderString)
        {
            $this->_loaderString = new Twig_Loader_String();
        }
        
        return $this->_loaderString;
    }
    
    /**
     * @see HelperInterface::setHelperContainer()
     */
    public function setHelperContainer($pValue)
    {
        $this->_helper = $pValue instanceof Container ? $pValue : new Container($pValue);
        $this->_helper->setView($this);
    }
    
    /**
     * @see HelperInterface::getHelperContainer()
     */
    public function getHelperContainer()
    {
        return $this->_helper;
    }
    
    /**
     * @see ViewInterface::getDefaultExtension()
     */
    public function getDefaultExtension()
    {
        return 'twig';
    }
    
    /**
     * @see HelperInterface::helper()
     * @throws \LogicException
     */
    public function helper($pKey)
    {
        throw new \LogicException('You can not execute this method in this context, go through a extension.');
    }

    /**
     * @see ViewInterface::render()
     */
    public function render($pTemplate, array $pData = array())
    {
        foreach($pData as $key => $value)
        {
            $this->set($key, $value);
        }
        
        foreach($this->_global as $key => $value)
        {
            $this->_environment->addGlobal($key, $this->get($key));
        }
        
        if($pTemplate instanceof Str)
        {
            $this->_environment->setLoader($this->getLoaderString());
        }
        else
        {
            $pTemplate = str_replace(
                APPLICATION_PATH, 
                '', 
                $pTemplate
            );
            
            $this->_environment->setLoader($this->getLoaderFilesystem());
        }
        
        $content = $this->_environment->render($pTemplate, $this->gets());
        
        foreach($pData as $key => $value)
        {
            $this->remove($key);
        }
        
        return $content;
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     */
    public function __call($pMethod, $pArguments) 
    {
        return call_user_func_array(array($this->_environment, $pMethod), $pArguments);
    }
}