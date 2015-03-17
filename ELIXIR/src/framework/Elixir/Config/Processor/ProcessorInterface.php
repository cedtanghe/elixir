<?php

namespace Elixir\Config\Processor;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface ProcessorInterface 
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function process($value);
}
