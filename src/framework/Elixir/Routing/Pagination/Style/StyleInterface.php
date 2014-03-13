<?php

namespace Elixir\Pagination\Style;

use Elixir\Pagination\PaginationInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */
interface StyleInterface 
{
    /**
     * @param PaginationInterface $pPagination
     * @return array
     */
    public function getRange(PaginationInterface $pPagination);
}
