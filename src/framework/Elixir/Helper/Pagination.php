<?php

namespace Elixir\Helper;

use Elixir\Pagination\PaginationInterface;
use Elixir\View\Helper\ContextInterface;
use Elixir\View\ViewInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Pagination implements ContextInterface, HelperInterface
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
     * @param PaginationInterface $pPagination
     * @param string $pFilePath
     * @return string
     */
    public function paginate(PaginationInterface $pPagination, $pFilePath)
    {
        if(null !== $this->_locator)
        {
            $pFilePath = $this->_locator->locateFile($pFilePath);
        }
        
        $view = clone $this->_context;
        
        return $view->render(
            $pFilePath, 
            array_merge(
                $pPagination->getParameters(), 
                array(
                    'pagination' => $pPagination, 
                    'range' => $pPagination->getPageRange()
                )
            )
        );
    }
    
    /**
     * @see Partial::render()
     */
    public function direct()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'paginate'), $args);
    }
}