<?php

namespace Elixir\Tree;

use Elixir\Util\Arr;

/**
 * @author Nicola Pertosa <n.pertosa@peoleo.fr>
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Tree implements TreeInterface
{
    /**
     * @var string
     */
    const CHILD_LEVEL = 'child_level';
    
    /**
     * @var array
     */
    protected $_parameters;
    
    /**
     * @var array 
     */
    protected $_trees;
    
    /**
     * @var integer
     */
    protected $_level = 0;
    
    /**
     * @var boolean
     */
    protected $_sorted = false;
    
    /**
     * @var integer
     */
    protected $_serial = 0;
    
    /**
     * @param array $pParameters
     * @param array $pChildren
     */
    public function __construct(array $pParameters = array(), array $pChildren = array())
    {
        $this->setParameters($pParameters);
        $this->setChildren($pChildren);
    }
    
    /**
     * @see TreeInterface::getLevel()
     */
    public function getLevel()
    {
        return $this->_level;
    }
    
    /**
     * @internal
     * @see TreeInterface::setLevel()
     */
    public function setLevel($pValue)
    {
        $this->_level = $pValue;
    }
    
    /**
     * @return boolean 
     */
    public function isRoot()
    {
        return $this->_level == 0;
    }
    
    /**
     * @param TreeInterface $pTree
     * @param integer $pPriority
     */
    public function addChild(TreeInterface $pTree, $pPriority = 0)
    {
        $this->_sorted = false;
        $this->_trees[] = array('tree' => $pTree,
                                'priority' =>$pPriority, 
                                'serial' => $this->_serial++);
    }
    
    /**
     * @see TreeInterface::getChildren()
     */
    public function getChildren($pWithInfos = false)
    {
        $trees = array();
            
        foreach($this->_trees as $value)
        {
            $trees[] = $pWithInfos ? $value : $value['tree'];
        }

        return $trees;
    }
    
    /**
     * @param array $pData
     */
    public function setChildren(array $pData)
    {
        $this->_trees = array();
        $this->_serial = 0;
        
        foreach($pData as $data)
        {
            $tree = $data;
            $priority = 0;
            
            if(is_array($data))
            {
                $tree = isset($data['tree']);
                
                if(isset($data['priority']))
                {
                    $priority = $data['priority'];
                }
            }
            
            $this->addChild($tree, $priority);
        }
    }
    
    /**
     * @return boolean 
     */
    public function hasChildren()
    {
        return count($this->_trees) > 0;
    }
    
    /**
     * @param mixed $pKey
     */
    public function hasParameter($pKey)
    {
        return Arr::has($pKey, $this->_parameters);
    }
    
    /**
     * @param mixed $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function getParameter($pKey, $pDefault = null)
    {
        return Arr::get($pKey, $this->_parameters, $pDefault);
    }
    
    /**
     * @param mixed $pKey
     * @param mixed $pValue
     */
    public function setParameter($pKey, $pValue)
    {
        Arr::set($pKey, $pValue, $this->_parameters);
    }
    
    /**
     * @param mixed $pKey
     */
    public function removeParameter($pKey)
    {
        Arr::remove($pKey, $this->_data);
    }
    
    /**
     * @see TreeInterface:getParameters()
     */
    public function getParameters()
    {
        return $this->_parameters;
    }
    
    /**
     * @param array $pData
     */
    public function setParameters(array $pData)
    {
        $this->_parameters = $pData;
    }
    
    /**
     * @see TreeInterface:sort()
     */
    public function sort()
    {
        if(!$this->_sorted)
        {
            usort($this->_trees, array($this, 'compare'));
            $this->_sorted = true;
        }
        
        foreach($this->getChildren() as $value)
        {
            $value->setLevel($this->_level + 1);
            $value->sort();
        }
    }
    
    /**
     * @param array $p1
     * @param array $p2
     * @return integer
     */
    protected function compare(array $p1, array $p2)
    {
        if ($p1['priority'] == $p2['priority']) 
        {
            return ($p1['serial'] < $p2['serial']) ? -1 : 1;
        }
        
        return ($p1['priority'] > $p2['priority']) ? -1 : 1;
    }
    
    /**
     * @see TreeInterface:find()
     */
    public function find(array $pParameters = array(), $pLevel = self::ALL_LEVEL, $pAll = false)
    {
        if($this->_level == 0)
        {
            $this->sort();
        }
        
        $return = null;
        
        if($pAll)
        {
            $return = array();
        }
        
        if($this->searchByParameters($pParameters))
        {
            if($pAll)
            {
                $return[] = $this;
            }
            else
            {
                return $this;
            }
        }
        
        if($pLevel < self::ALL_LEVEL)
        {
            $pLevel = self::ALL_LEVEL;
        }
        
        if($pLevel == self::ALL_LEVEL || $pLevel > 0)
        {
            --$pLevel;
            
            foreach($this->getChildren() as $value)
            {
                if($pAll)
                {
                    $return = array_merge($value->find($pParameters, $pLevel, $pAll), $return);
                }
                else if(null === $return)
                {
                    $return = $value->find($pParameters, $pLevel, $pAll);
                }
            }
        }
        
        return $return;
    }
    
    /**
     * @param array $pParameters
     * @return boolean
     */
    protected function searchByParameters(array $pParameters)
    {
        $count = 0;
        $total = count($pParameters);
        
        if($total == 0)
        {
            return true;
        }
        
        foreach($pParameters as $key => $value)
        {
            if($key == self::CHILD_LEVEL)
            {
                if($value == $this->_level)
                {
                    $count++;
                    
                    if($count == $total)
                    {
                        return true;
                    }
                }
            }
            else if($this->hasParameter($key))
            {
                if($this->getParameter($key) == $value)
                {
                    $count++;

                    if($count == $total)
                    {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
}
