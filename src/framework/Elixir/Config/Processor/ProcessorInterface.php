<?php

namespace Elixir\Config\Processor;

use Elixir\Config\ConfigInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
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
