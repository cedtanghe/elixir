<?php

namespace ElixirTest\DI;

use Elixir\DI\ContainerInterface;

class Definition
{
    public function __invoke(ContainerInterface $pContainer)
    {
        return 'This is a definition';
    }
}
