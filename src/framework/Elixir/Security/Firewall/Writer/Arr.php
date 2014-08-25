<?php

namespace Elixir\Security\Firewall\Writer;

use Elixir\Security\Firewall\Writer\WriterAbstract;

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
        $data = [];
        
        if(method_exists($this->_firewall, 'sort'))
        {
            $this->_firewall->sort();
        }
        
        foreach($this->_firewall->getAccessControls(true) as $value)
        {
            $data[] = [
                'regex' => $value['accessControl']->getPattern(),
                'options' => $value['accessControl']->getOptions(),
                'priority' => $value['priority'],
            ];
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
