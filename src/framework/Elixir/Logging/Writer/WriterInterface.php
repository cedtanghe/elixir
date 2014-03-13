<?php

namespace Elixir\Logging\Writer;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface WriterInterface
{
    /**
     * @param string $pMessage
     * @param integer $pSeverity
     */
    public function write($pMessage, $pSeverity);
}