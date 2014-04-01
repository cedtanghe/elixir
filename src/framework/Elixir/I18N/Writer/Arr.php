<?php

use Elixir\Config\I18N\WriterAbstract;

namespace Elixir\Config\I18N;

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
        $data = array();
        
        foreach($this->_I18N->getTextDomains() as $key => $domain)
        {
            $data[$key] = $domain->gets(true);
        }
        
        return $data;
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