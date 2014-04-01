<?php

namespace Elixir\Helper;

use Elixir\MVC\ApplicationInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Locator implements HelperInterface
{
    /**
     * @var ApplicationInterface 
     */
    protected $_application;
    
    /**
     * @param ApplicationInterface $pApplication
     */
    public function __construct(ApplicationInterface $pApplication)
    {
        $this->_application = $pApplication;
    }

    /**
     * @see ApplicationInterface::locateClass()
     */
    public function locateClass($pClassName)
    {
        return $this->_application->locateClass($pClassName);
    }
    
    /**
     * @see ApplicationInterface::locateFile()
     */
    public function locateFile($pFilePath, $pAll = false)
    {
        return $this->_application->locateFile($pFilePath, $pAll);
    }
    
    /**
     * @see Locator::locateFile()
     */
    public function direct()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'locateFile'), $args);
    }
}
