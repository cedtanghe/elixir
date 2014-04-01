<?php

namespace Elixir\Config\Writer;

use Elixir\Config\ConfigInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface WriterInterface 
{
    /**
     * @param ConfigInterface $pValue
     */
    public function setConfig(ConfigInterface $pValue);

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