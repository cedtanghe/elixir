<?php

namespace Elixir\Module\Console\Command;

use Elixir\DB\DBInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface SeederInterface
{
    /**
     * @param DBInterface $pDB
     */
    public function seed(DBInterface $pDB);
}