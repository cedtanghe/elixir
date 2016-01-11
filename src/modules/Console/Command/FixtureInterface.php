<?php

namespace Elixir\Module\Console\Command;

use Elixir\DB\DBInterface;
use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface FixtureInterface
{
    /**
     * @param ContainerInterface $pContainer
     */
    public function setContainer(ContainerInterface $pContainer);
    
    /**
     * @param DBInterface $pDB
     * @param mixed $pOutput
     */
    public function load(DBInterface $pDB, $pOutput = null);
    
    /**
     * @return integer
     */
    public function getOrder();
}
