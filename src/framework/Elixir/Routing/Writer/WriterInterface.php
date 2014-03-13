<?php

namespace Elixir\Routing\Writer;

use Elixir\Routing\RouterInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface WriterInterface 
{
    /**
     * @param RouterInterface $pValue
     */
    public function setRouter(RouterInterface $pValue);

    /**
     * @return mixed
     */
    public function write();
    
    /**
     * @param string $pFile
     * @return boolean
     */
    public function export($pFile);  
}