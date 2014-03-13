<?php

namespace Elixir\Config\Writer;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
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