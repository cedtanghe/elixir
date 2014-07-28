<?php

namespace Elixir\Module\Console\Command;

use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface SeederInterface
{
    /**
     * @param ContainerInterface $pContainer
     */
    public function seed(ContainerInterface $pContainer);
}