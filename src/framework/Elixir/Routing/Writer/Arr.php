<?php

namespace Elixir\Routing\Writer;

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
        
        $collection = $this->_router->getCollection();
        $collection->sort();
        
        foreach($collection->gets(true) as $key => $value)
        {
            $data[$key] = array(
                'regex' => $value['route']->getPattern(),
                'parameters' => $value['route']->getParameters(),
                'options' => $value['route']->getOptions(),
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