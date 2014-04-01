<?php

namespace Elixir\Pagination\Style;

use Elixir\Pagination\Item;
use Elixir\Pagination\PaginationInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Jumping implements StyleInterface
{
    /**
     * @see StyleInterface::getRange()
     */
    public function getRange(PaginationInterface $pPagination)
    {
        $pageRange  = $pPagination->getItemsPerPage();
        $totalItems = $pPagination->getTotalItems();
        $currentPage = $pPagination->getCurrentPage();
        $currentItem = $pPagination->getCurrentItem();
        $lowerBound = ($currentPage > 0 ? $currentPage - 1 : 0) * $pageRange;
        $upperBound = $lowerBound + $pageRange;
        
        if($upperBound > $totalItems)
        {
            $upperBound = $totalItems;
        }
        
        $range = array();
        
        for($i = $lowerBound; $i < $upperBound; ++$i)
        {
            $index = $i + 1;
            
            $item = new Item($index);
            $item->setSelected($index == $currentItem);
            $item->setFirst($index == $lowerBound);
            $item->setLast($index == $upperBound);
            
            $range[] = $item;
        }
        
        return $range;
    }
}
