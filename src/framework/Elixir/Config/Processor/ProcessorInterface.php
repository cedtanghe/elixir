<?php

namespace Elixir\Config\Processor;

use Elixir\Config\ConfigInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface ProcessorInterface 
{
    /**
     * @param ConfigInterface $pValue
     */
    public function processConfig(ConfigInterface $pConfig);
    
    /**
     * @param mixed $pValue
     * @return mixed
     */
    public function process($pValue);
}
