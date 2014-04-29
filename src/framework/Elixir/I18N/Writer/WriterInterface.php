<?php

namespace Elixir\I18N\Writer;

use Elixir\I18N\I18NInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface WriterInterface 
{
    /**
     * @param I18NInterface $pValue
     */
    public function setI18N(I18NInterface $pValue);

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