<?php

namespace Elixir\View\PHP;

use Elixir\View\DataAbstract;
use Elixir\View\Helper\Container;
use Elixir\View\HelperInterface;
use Elixir\View\ViewInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class PHP extends DataAbstract implements HelperInterface
{
    /**
     * @var string 
     */
    protected $_parent;
    
    /**
     * @var Blocks
     */
    protected $_blocks;
    
    /**
     * @var Container
     */
    protected $_helper;
    
    /**
     * @var Parser
     */
    protected $_parser;
    
    public function __construct() 
    {
        $this->_blocks = new Blocks();
        $this->_parser = new Parser($this);
    }
    
    /**
     * @return Blocks
     */
    public function getBlocks()
    {
        return $this->_blocks;
    }
    
    /**
     * @return Parser
     */
    public function getParser()
    {
        return $this->_parser;
    }

    /**
     * @see HelperInterface::setHelperContainer()
     */
    public function setHelperContainer($pValue)
    {
        $this->_helper = $pValue instanceof Container ? $pValue : new Container($pValue);
        $this->_helper->setView($this);
    }
    
    /**
     * @see HelperInterface::getHelperContainer()
     */
    public function getHelperContainer()
    {
        return $this->_helper;
    }
    
    /**
     * @see HelperInterface::helper()
     */
    public function helper($pKey)
    {
        return $this->_helper->get($pKey);
    }
    
    /**
     * @param string $pTemplate
     */
    public function extend($pTemplate)
    {
        $this->_parent = $pTemplate;
    }
    
    /**
     * @param string $pBlock
     */
    public function start($pBlock)
    {
        $this->_blocks->start($pBlock);
    }
    
    /**
     * @return string
     */
    public function parent()
    {
        return $this->_blocks->parent();
    }
    
    public function end()
    {
        $this->_blocks->end();
    }
    
    /**
     * @param string $pBlock
     * @return string
     */
    public function block($pBlock)
    {
        return $this->_blocks->mask($pBlock, '');
    }
    
    /**
     * @see ViewInterface::render()
     */
    public function render($pTemplate, array $pData = array())
    {
        foreach($pData as $key => $value)
        {
            $this->set($key, $value);
        }
        
        $this->_parent = null;
        
        try
        {
            $content = $this->_parser->parse($pTemplate);
        }
        catch(\Exception $e)
        {
            $this->_blocks->reset();
            throw $e;
        }
        
        if(!empty($this->_parent))
        {
            $this->set('_content', $content);
            $content = $this->render($this->_parent);
            $this->remove('_content');
        }
        else
        {
            $content = $this->_blocks->parse($content);
        }
        
        foreach($pData as $key => $value)
        {
            $this->remove($key);
        }
        
        return $content;
    }
    
    public function __clone() 
    {
        parent::__clone();
        $this->_parser = new Parser($this);
    }
}
