<?php

namespace Elixir\Logging\Writer;

use Elixir\Util\File as FileUtils;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class File extends WriterAbstract
{
    /**
     * @var resource 
     */
    protected $_handler;
    
    /**
     * @param string $pPath
     */
    public function __construct($pPath = 'application/logging/log.txt')
    {
        $dirname = FileUtils::dirname($pPath);
        
        if(!is_dir(dirname($dirname)))
        {
            @mkdir(dirname($dirname), 0777, true);
        }
        
        $this->_handle = @fopen($pPath, 'a+');
    }
    
    public function __destruct() 
    {
        fclose($this->_handle);
    }
    
    /**
     * @see WriterAbstract::clear()
     */
    public function clear()
    {
        return ftruncate($this->_handle, 0);
    }

    /**
     * @see WriterInterface::write()
     */
    public function write($pMessage, $pSeverity)
    {
        if(!$this->isLocked($pSeverity))
        {
            fwrite($this->_handle, $this->format($pMessage, $pSeverity) . "\n\n");
        }
    }
}
