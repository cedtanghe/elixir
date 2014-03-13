<?php

namespace ElixirTest\DI;

use Elixir\DI\ContainerInterface;

class Extend
{
    public function __invoke($pService, ContainerInterface $pContainer)
    {
        $pService['data'] = 'This is an extension';
        return $pService;
    }
}
