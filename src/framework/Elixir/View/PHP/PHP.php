<?php

namespace Elixir\View\PHP;

use Elixir\Filter\FilterInterface;
use Elixir\View\DataAbstract;
use Elixir\View\EscaperInterface;
use Elixir\View\Helper\Container;
use Elixir\View\HelperInterface;
use Elixir\View\PHP\Blocks;
use Elixir\View\PHP\Parser;
use Elixir\View\ViewInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class PHP extends DataAbstract implements HelperInterface, EscaperInterface
{
    /**
     * @var string
     */
    const CONTENT_KEY = '_content';
    
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
     * @see ViewInterface::getDefaultExtension()
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
                $pData = $this->_escaper->filter($pData, ['strategy' => $pStrategy]);
            }
        }
        
        return $pData;
    }
    
    /**
     * @see EscaperInterface::raw()
     */
    public function raw($pKey, $pDefault = null)
    {
        return parent::get($pKey, $pDefault);
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
        if (false !== strpos($module, '(@') && $this->helper('helper.locator'))
        {
            $pTemplate = $this->helper('helper.locator')->locateFile($pTemplate);
        }
        
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
        $value = $this->raw($pKey, $pDefault);
        
        if($this->_autoEscape && $pKey !== self::CONTENT_KEY)
        {
            $value = $this->escape($value);
        }
        
        return $value;
    }

    /**
     * @see ViewInterface::render()
     */
    public function render($pTemplate, array $pData = [])
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
            $this->set(self::CONTENT_KEY, $content);
            $content = $this->render($this->_parent);
            $this->remove(self::CONTENT_KEY);
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
