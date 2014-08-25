<?php

namespace Elixir\View\PHP;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class BlockEvent extends Event
{
    /**
     * @var string
     */
    const COMPILE_BLOCK = 'compile_block';
    
    /**
     * @var string
     */
    protected $_block;
    
    /**
     * @var string
     */
    protected $_content;
    
    /**
     * @var array
     */
    protected $_options;
    
    /**
     * @see Event::__contruct()
     * @param string $pBlock
     * @param string $pContent
     * @param array $pOptions
     */
    public function __construct($pType, $pBlock = null, $pContent = null, array $pOptions = []) 
    {
        parent::__construct($pType);
        
        $this->_block = $pBlock;
        $this->_content = $pContent;
        $this->_options = $pOptions;
    }
    
    /**
     * @return string
     */
    public function getBlock()
    {
        return $this->_block;
    }
    
    /**
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }
    
    /**
     * @param string $pValue
     */
    public function setContent($pValue)
    {
        $this->_content = $pValue;
    }
    
    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
}
