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
        return $this->config->gets();
    }

    /**
     * @see WriterAbstract::export()
     */
    public function export($file)
    {
        if (!strstr($file, '.php'))
        {
            $file .= '.php';
        }
        
        file_put_contents($file, '<?php return ' . var_export($this->write(), true) . ';');
        return file_exists($file);
    }
}
