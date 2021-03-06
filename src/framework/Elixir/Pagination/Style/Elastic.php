<?php

namespace Elixir\Pagination\Style;

use Elixir\Pagination\Item;
use Elixir\Pagination\PaginationInterface;
use Elixir\Pagination\Style\StyleInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Elastic implements StyleInterface
{
    /**
     * @see StyleInterface::getRange()
     */
    public function getRange(PaginationInterface $pPagination)
    {
        $pageRange  = $pPagination->getItemsPerPage();
        $totalItems = $pPagination->getTotalItems();
        $currentItem = $pPagination->getCurrentItem();
        $delta = $pageRange / 2;
        $lowerBound = $currentItem - $delta < 0 ? 0 : floor($currentItem - $delta);
        $upperBound = $currentItem + $delta < $pageRange ? $pageRange : ceil($currentItem + $delta);
        
        if($upperBound > $totalItems)
        {
            $upperBound = $totalItems;
        }
        
        if($upperBound - $lowerBound < $pageRange)
        {
            $lowerBound = $upperBound - $pageRange;
            
            if($lowerBound < 0)
            {
                $lowerBound = 0;
            }
        }
        
        $range = [];
        
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
