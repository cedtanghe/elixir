<?php

namespace Elixir\View\Storage;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Str implements StorageInterface
{
    /**
     * @var string
     */
    protected $_str;

    /**
     * @param string $pStr
     */
    public function __construct($pStr) 
    {
        $this->_str = $pStr;
    }
    
    /**
     * @see StorageInterface::getContent()
     */
    public function getContent()
    {
        return $this->_str;
    }
    
    /**
     * @see Str::getContent()
     */
    public function __toString() 
    {
        return $this->getContent();
    }
}
