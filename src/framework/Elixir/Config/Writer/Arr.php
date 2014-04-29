<?php

namespace Elixir\Config\Writer;

use Elixir\Config\Writer\WriterAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Arr extends WriterAbstract
{
    /**
     * @see WriterAbstract::write()
     */
    public function write()
    {
        return $this->_config->gets();
    }
    
    /**
     * @see WriterAbstract::export()
     */
    public function export($pFile)
    {
        file_put_contents($pFile, '<?php return ' . var_export($this->write(), true));
        return file_exists($pFile);
    }
}