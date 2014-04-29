<?php

namespace Elixir\Pagination;

use Elixir\Pagination\PaginationInterface;
use Elixir\Pagination\Style\StyleInterface;
use Elixir\Util\Arr;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Pagination implements PaginationInterface
{
    /**
     * @var integer 
     */
    const DEFAULT_ITEMS_PER_PAGE = 10;
    
    /**
     * @var array 
     */
    protected $_parameters = array();
    
    /**
     * @var integer 
     */
    protected $_itemsPerPage = self::DEFAULT_ITEMS_PER_PAGE;
    
    /**
     * @var integer 
     */
    protected $_totalItems = 0;
    
    /**
     * @var integer 
     */
    protected $_totalPages = 0;
    
    /**
     * @var integer 
     */
    protected $_currentItem = 0;
    
    /**
     * @var integer 
     */
    protected $_currentPage = 0;
    
    /**
     * @var StyleInterface 
     */
    protected $_style;
    
    /**
     * @param StyleInterface $pStyle
     */
    public function __construct(StyleInterface $pStyle) 
    {
        $this->_style = $pStyle;
    }
    
    /**
     * @see PaginationInterface::getStyle()
     */
    public function getStyle()
    {
        return $this->_style;
    }
    
    /**
     * @see PaginationInterface::setItemsPerPage()
     */
    public function setItemsPerPage($pValue)
    {
        $this->_itemsPerPage = $pValue;
        $this->setTotalItems($this->_totalItems);
    }
    
    /**
     * @see PaginationInterface::getItemsPerPage()
     */
    public function getItemsPerPage()
    {
        return $this->_itemsPerPage;
    }
    
    /**
     * @see PaginationInterface::setTotalItems()
     */
    public function setTotalItems($pValue)
    {
        $this->_totalItems = $pValue;
        $this->_totalPages = ceil($this->_totalItems / $this->_itemsPerPage);
        $this->setCurrentItem($this->_currentItem);
    }
    
    /**
     * @see PaginationInterface::getTotalItems()
     */
    public function getTotalItems()
    {
        return $this->_totalItems;
    }
    
    /**
     * @see PaginationInterface::getTotalPages()
     */
    public function getTotalPages()
    {
        return $this->_totalPages;
    }
    
    /**
     * @see PaginationInterface::setCurrentItem()
     */
    public function setCurrentItem($pValue)
    {
        $pValue = (int)$pValue;
        
        if($pValue > $this->getTotalItems())
        {
            $pValue = $this->getTotalItems();
        }
        
        if($pValue < 1)
        {
            $pValue = 1;
        }
        
        $this->_currentItem = $pValue;
        $this->_currentPage = ceil($this->_currentItem / $this->_itemsPerPage);
    }
    
    /**
     * @see PaginationInterface::getCurrentItem()
     */
    public function getCurrentItem()
    {
        return $this->_currentItem;
    }
    
    /**
     * @see PaginationInterface::hasNextItem()
     */
    public function hasNextItem()
    {
        return $this->_currentItem < $this->_totalItems;
    }

    /**
     * @see PaginationInterface::nextItem()
     */
    public function nextItem() 
    {
        if($this->hasNextItem())
        {
            $this->setCurrentItem($this->_currentItem + 1);
        }
    }
    
    /**
     * @see PaginationInterface::hasPreviousItem()
     */
    public function hasPreviousItem()
    {
        return $this->_currentItem > 1;
    }
    
    /**
     * @see PaginationInterface::previousItem()
     */
    public function previousItem() 
    {
        if($this->hasPreviousItem())
        {
            $this->setCurrentItem($this->_currentItem - 1);
        }
    }
    
    /**
     * @see PaginationInterface::setCurrentPage()
     */
    public function setCurrentPage($pValue)
    {
        $this->setCurrentItem($pValue * $this->_itemsPerPage);
    }
    
    /**
     * @see PaginationInterface::getCurrentPage()
     */
    public function getCurrentPage()
    {
        return $this->_currentPage;
    }
    
    /**
     * @see PaginationInterface::hasNextPage()
     */
    public function hasNextPage()
    {
        return $this->_currentPage < $this->_totalPages;
    }
    
    /**
     * @see PaginationInterface::nextPage()
     */
    public function nextPage() 
    {
        if($this->hasNextPage())
        {
            $this->setCurrentPage($this->_currentPage + 1);
        }
    }
    
    /**
     * @see PaginationInterface::hasPreviousPage()
     */
    public function hasPreviousPage()
    {
        return $this->_currentPage > 1;
    }
    
    /**
     * @see PaginationInterface::previousPage()
     */
    public function previousPage() 
    {
        if($this->hasPreviousPage())
        {
            $this->setCurrentPage($this->_currentPage - 1);
        }
    }
    
    /**
     * @param mixed $pKey
     */
    public function hasParameter($pKey)
    {
        return Arr::has($pKey, $this->_parameters);
    }
    
    /**
     * @param mixed $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function getParameter($pKey, $pDefault = null)
    {
        return Arr::get($pKey, $this->_parameters, $pDefault);
    }
    
    /**
     * @param mixed $pKey
     * @param mixed $pValue
     */
    public function setParameter($pKey, $pValue)
    {
        Arr::set($pKey, $pValue, $this->_parameters);
    }
    
    /**
     * @param mixed $pKey
     */
    public function removeParameter($pKey)
    {
        Arr::remove($pKey, $this->_data);
    }
    
    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }
    
    /**
     * @param array $pData
     */
    public function setParameters(array $pData)
    {
        $this->_parameters = $pData;
    }
    
    /**
     * @see PaginationInterface::getPageRange()
     */
    public function getPageRange()
    {
        return $this->_style->getRange($this);
    }
}