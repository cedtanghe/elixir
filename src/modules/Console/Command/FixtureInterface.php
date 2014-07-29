<?php

namespace Elixir\Module\Console\Command;

use Elixir\DB\DBInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface FixtureInterface
{
    /**
     * @param DBInterface $pDB
     */
    public function load(DBInterface $pDB);
    
    /**
     * @return integer
     */
    public function getOrder();
}