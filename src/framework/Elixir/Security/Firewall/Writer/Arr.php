<?php

namespace Elixir\Security\Firewall\Writer;

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
        $data = array();
        
        if(method_exists($this->_firewall, 'sort'))
        {
            $this->_firewall->sort();
        }
        
        foreach($this->_firewall->getAccessControls(true) as $value)
        {
            $data[] = array(
                'regex' => $value['accessControl']->getPattern(),
                'options' => $value['accessControl']->getOptions(),
                'priority' => $value['priority'],
            );
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