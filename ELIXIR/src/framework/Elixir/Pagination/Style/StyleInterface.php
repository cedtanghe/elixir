<?php

namespace Elixir\Pagination\Style;

use Elixir\Pagination\PaginationInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface StyleInterface 
{
    /**
     * @param PaginationInterface $pPagination
     * @return array
     */
    public function getRange(PaginationInterface $pPagination);
}
