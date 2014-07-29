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
     */
    public function load(DBInterface $pDB);
    
    /**
     * @return integer
     */
    public function getOrder();
}