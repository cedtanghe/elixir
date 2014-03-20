<?php

namespace Elixir\View\PHP;

use Elixir\Filter\FilterInterface;
use Elixir\View\DataAbstract;
use Elixir\View\EscaperInterface;
use Elixir\View\Helper\Container;
use Elixir\View\HelperInterface;
use Elixir\View\ViewInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class PHP extends DataAbstract implements HelperInterface, EscaperInterface
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
    
    /**
     * @var FilterInterface 
     */
    protected $_escaper;
    
    /**
     * @var boolean 
     */
    protected $_autoEscape = false;
    
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
     * @return string
     */
    public function getDefaultExtension()
    {
        return 'phtml';
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
     * @param FilterInterface $pValue
     */
    public function setEscaper(FilterInterface $pValue)
    {
        $this->_escaper = $pValue;
    }
    
    /**
     * @return FilterInterface
     */
    public function getEscaper()
    {
        return $this->_escaper;
    }
    
    /**
     * @see EscaperInterface::setAutoEscape()
     */
    public function setAutoEscape($pValue)
    {
        $this->_autoEscape = $pValue;
    }
    
    /**
     * @see EscaperInterface::isAutoEscape()
     */
    public function isAutoEscape()
    {
        return $this->_autoEscape;
    }

    /**
     * @see EscaperInterface::escape()
     */
    public function escape($pData, $pStrategy = 'html')
    {
        if(null !== $this->_escaper)
        {
            if(is_array($pData) || is_object($pData) || $pData instanceof \Traversable)
            {
                foreach($pData as &$value)
                {
                    $value = $this->escape($value, $pStrategy);
                }
            }
            else
            {
                $pData = $this->_escaper->filter($pData, array('strategy' => $pStrategy));
            }
        }
        
        return $pData;
    }
    
    /**
     * @see EscaperInterface::raw()
     */
    public function raw($pKey, $pDefault = null)
    {
        if($this->has($pKey))
        {
            return $this->_vars[$pKey];
        }
        
        if(is_callable($pDefault))
        {
            return call_user_func($pDefault);
        }
        
        return $pDefault;
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
     * @see ViewInterface::get()
     */
    public function get($pKey, $pDefault = null) 
    {
        $value = parent::get($pKey, $pDefault);
        
        if($this->_autoEscape)
        {
            $value = $this->escape($value);
        }
        
        return $value;
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
