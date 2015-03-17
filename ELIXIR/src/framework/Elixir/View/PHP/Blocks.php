<?php

namespace Elixir\View\PHP;

use Elixir\Dispatcher\Dispatcher;
use Elixir\View\PHP\BlockEvent;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Blocks extends Dispatcher
{
    /**
     * @var array
     */
    protected $_blocks = [];
    
    /**
     * @var array
     */
    protected $_contents = [];
    
    /**
     * @var array
     */
    protected $_options = [];
    
    /**
     * @var array
     */
    protected $_compiled = [];
    
    /**
     * @var string 
     */
    protected $_block;

    /**
     * @param string $pKey
     * @param array $pOptions
     * @throws \LogicException
     */
    public function start($pKey, array $pOptions = [])
    {
        if(in_array($pKey, $this->_blocks))
        {
            throw new \LogicException(sprintf('A block "%s" is already started.', $pKey));
        }
        
        unset($this->_compiled[$pKey]);
        
        $this->_block = $pKey;
        $this->_blocks[] = $this->_block;
        $this->_contents[$pKey][] = '';
        $this->_options[$pKey] = array_merge(
            isset($this->_options[$pKey]) ? $this->_options[$pKey] : [], 
            $pOptions
        );
        
        ob_start();
    }
    
    /**
     * @return string
     */
    public function parent()
    {
        return '{PARENT_BLOCK}';
    }
    
    /**
     * @throws \LogicException
     */
    public function end()
    {
        if(count($this->_blocks) == 0)
        {
            throw new \LogicException('No block has been started.');
        }
        
        $block = array_pop($this->_blocks);
        $this->_contents[$block][count($this->_contents[$block]) - 1] = ob_get_clean();
        $this->_block = null;
    }
    
    /**
     * @param string $pKey
     * @return mixed
     */
    public function mask($pKey, $pDefault = null)
    {
        if($this->has($pKey))
        {
            return sprintf('{BLOCK : %s}', $pKey);
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }

    /**
     * @param string $pKey
     * @return boolean
     */
    public function has($pKey)
    {
        return isset($this->_contents[$pKey]);
    }

    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @param boolean $pCompiled
     * @return mixed
     */
    public function get($pKey, $pDefault = null, $pCompiled = true)
    {
        if($this->_block != $pKey && $this->has($pKey))
        {
            if(!$pCompiled)
            {
                return [
                    'contents' => $this->_contents[$pKey],
                    'options' => $this->_options[$pKey]
                ];
            }
            
            if(isset($this->_compiled[$pKey]))
            {
                return $this->_compiled[$pKey];
            }
            
            $contents = array_reverse(array_slice($this->_contents[$pKey], 0));
            $content = '';
            $replace = '';
            
            while(count($contents) > 0)
            {
                $content = str_replace($this->parent(), $replace, array_shift($contents));
                $replace = $content;
            }
            
            if(false !== strpos($content, '{BLOCK :'))
            {
                if(preg_match_all('/{BLOCK : (.+)}/', $content, $matches))
                {
                    foreach($matches[1] as $block)
                    {
                        $content = str_replace($this->mask($block), $this->get($block, ''), $content);
                    }
                }
            }
           
            $event = new BlockEvent(BlockEvent::COMPILE_BLOCK, $pKey, $content, $this->_options[$pKey]);
            $this->dispatch($event);
            $content = $event->getContent();
            $this->_compiled[$pKey] = $content;
            
            return $content;
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @param string $pKey
     * @param string $pValue
     * @param array $pOptions
     * @param boolean $pReplace
     */
    public function set($pKey, $pValue, array $pOptions = [], $pReplace = true)
    {
        $pos = array_search($pKey, $this->_blocks);
        
        if(false !== $pos)
        {
            array_splice($this->_blocks, $pos, 1);
            ob_get_clean();
        }
        
        unset($this->_compiled[$pKey]);
        
        if($pReplace)
        {
            $this->_contents[$pKey] = [];
            $this->_options[$pKey] = [];
        }
        
        $this->_options[$pKey] = array_merge(
            isset($this->_options[$pKey]) ? $this->_options[$pKey] : [], 
            $pOptions
        );
        
        $this->_contents[$pKey][] = $pValue;
    }
    
    /**
     * @param string $pKey
     */
    public function remove($pKey)
    {
        $pos = array_search($pKey, $this->_blocks);
        
        if(false !== $pos)
        {
            array_splice($this->_blocks, $pos, 1);
            ob_get_clean();
        }
        
        unset($this->_compiled[$pKey]);
        unset($this->_contents[$pKey]);
        unset($this->_options[$pKey]);
    }
    
    /**
     * @return array
     */
    public function gets()
    {
        $blocks = [];
        
        foreach($this->_contents as $key => $value)
        {
            $blocks[$key] = [
                'contents' => $value,
                'options' => $this->_options[$key]
            ];
        }
        
        return $blocks;
    }
    
    /**
     * @param array $pData
     */
    public function sets(array $pData)
    {
        $this->reset();
        
        foreach($pData as $key => $value)
        {
            if(is_array($value))
            {
                $this->set(
                    $key, 
                    $value['content'], 
                    isset($value['options']) ? $value['options'] : []
                );
            }
            else
            {
                $this->set($key, $value);
            }
        }
    }
    
    public function reset()
    {
        $i = count($this->_blocks);
        
        while($i--)
        {
            ob_end_clean();
        }
        
        $this->_compiled = [];
        $this->_blocks = [];
        $this->_contents = [];
        $this->_options = [];
    }

    /**
     * @param string $pContent
     * @return string
     */
    public function parse($pContent)
    {
        foreach($this->_contents as $key => $value)
        {
            $pContent = str_replace($this->mask($key), $this->get($key, ''), $pContent);
        }
        
        return $pContent;
    }
}
