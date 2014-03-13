<?php

namespace Elixir\Pagination;

use Elixir\Pagination\Style\StyleInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface PaginationInterface 
{
    /**
     * @return StyleInterface
     */
    public function getStyle();
    
    /**
     * @param integer $pValue
     */
    public function setItemsPerPage($pValue);
    
    /**
     * @return integer
     */
    public function getItemsPerPage();
    
    /**
     * @param integer $pValue
     */
    public function setTotalItems($pValue);
    
    /**
     * @return integer
     */
    public function getTotalItems();
    
    /**
     * @return integer
     */
    public function getTotalPages();
    
    /**
     * @param integer $pValue
     */
    public function setCurrentItem($pValue);
    
    /**
     * @return integer
     */
    public function getCurrentItem();
    
    /**
     * @return boolean
     */
    public function hasNextItem();
    
    public function nextItem();
    
    /**
     * @return boolean
     */
    public function hasPreviousItem();
    
    public function previousItem();
    
    /**
     * @param integer $pValue
     */
    public function setCurrentPage($pValue);
    
    /**
     * @return integer
     */
    public function getCurrentPage();
    
    /**
     * @return boolean
     */
    public function hasNextPage();
    
    public function nextPage();
    
    /**
     * @return integer
     */
    public function hasPreviousPage();
    
    public function previousPage();
    
    /**
     * @retunr array
     */
    public function getParameters();
    
    /**
     * @return array
     */
    public function getPageRange();
}
