<?php

namespace Elixir\Dispatcher;

use Elixir\Dispatcher\DispatcherInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Dispatcher implements DispatcherInterface
{
    use DispatcherTrait;

    /**
     * @param mixed $target 
     */
    public function __construct($target = null)
    {
        $this->setTarget($target);
    }
}
