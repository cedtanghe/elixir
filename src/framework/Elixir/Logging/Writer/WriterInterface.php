<?php

namespace Elixir\Logging\Writer;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface WriterInterface
{
    /**
     * @param string $pMessage
     * @param integer $pSeverity
     */
    public function write($pMessage, $pSeverity);
}