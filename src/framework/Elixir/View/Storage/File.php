<?php

namespace Elixir\View\Storage;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class File implements StorageInterface
{
    /**
     * @var string
     */
    protected $_file;

    /**
     * @param string $pFile
     */
    public function __construct($pFile) 
    {
        $this->_file = $pFile;
    }
    
    /**
     * @see StorageInterface::getContent()
     */
    public function getContent()
    {
        return $this->_file;
    }
    
    /**
     * @see File::getContent()
     */
    public function __toString() 
    {
        return $this->getContent();
    }
}
