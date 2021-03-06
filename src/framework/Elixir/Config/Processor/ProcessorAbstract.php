<?php

namespace Elixir\Config\Processor;

use Elixir\Config\ConfigInterface;
use Elixir\Config\Processor\ProcessorInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class ProcessorAbstract implements ProcessorInterface
{
    /**
     * @see ProcessorInterface::processConfig()
     */
    public function processConfig(ConfigInterface $pConfig)
    {
        $pConfig->sets($this->process($pConfig->gets()));
    }
}
