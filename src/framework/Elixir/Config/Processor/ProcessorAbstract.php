<?php

namespace Elixir\Config\Processor;

use Elixir\Config\ConfigInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
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
