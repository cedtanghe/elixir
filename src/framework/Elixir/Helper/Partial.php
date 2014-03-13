<?php

namespace Elixir\Helper;

use Elixir\View\Helper\ContextInterface;
use Elixir\View\Storage\StorageInterface;
use Elixir\View\ViewInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Partial implements ContextInterface, HelperInterface
{
    /**
     * @var ViewInterface
     */
    protected $_context;
    
    /**
     * @var Locator
     */
    protected $_locator;

    /**
     * @param ViewInterface $pValue
     */
    public function setView(ViewInterface $pValue)
    {
        $this->_context = $pValue;
    }
    
    /**
     * @param Locator $pValue
     */
    public function setLocator(Locator $pValue)
    {
        $this->_locator = $pValue;
    }
    
    /**
     * @return Locator
     */
    public function getLocator()
    {
        return $this->_locator;
    }

    /**
     * @param string|StorageInterface $pTemplate
     * @param array $pParams
     * @return string
     * @throws \InvalidArgumentException
     */
    public function renderLoop($pTemplate, array $pParams = array())
    {
        if(null !== $this->_locator)
        {
            if(is_string($pTemplate))
            {
                $pTemplate = $this->_locator->locateFile($pTemplate);
            }
        }
        
        $view = clone $this->_context;
        $content = '';
        $c = 0;
        $total = count($pParams);
        
        foreach($pParams as $row)
        {
            if(null === $row)
            {
                $row = array();
            }
            else if(!is_array($row))
            {
                if(is_object($row))
                {
                    if(method_exists($row, 'export'))
                    {
                        $row = $row->export();
                    }
                    else
                    {
                        $row = get_object_vars($row);
                    }
                }
                else
                {
                    throw new \InvalidArgumentException('Parameters to make the view are not valid.');
                }
            }
            
            $row['_loopIndex'] = $c++;
            $row['_loopTotal'] = $total;
            
            $content .= $view->render($pTemplate, $row);
        }
        
        return $content;
    }
    
    /**
     * @see Partial::renderLoop()
     */
    public function render($pTemplate, array $pParams = array())
    {
        return $this->renderLoop($pTemplate, array($pParams));
    }
    
    /**
     * @see Partial::render()
     */
    public function direct()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'render'), $args);
    }
}