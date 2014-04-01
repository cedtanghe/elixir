<?php

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class PHPChecker_Collection implements IteratorAggregate
{
    /**
     * @var array 
     */
    protected $_requirements = array();

    /**
     * @param PHPChecker_Requirement $pRequirement
     * @return boolean
     */
    public function has(PHPChecker_Requirement $pRequirement)
    {
        return in_array($pRequirement, $this->_requirements, true);
    }
    
    /**
     * @param PHPChecker_Requirement $pRequirement
     */
    public function add(PHPChecker_Requirement $pRequirement)
    {
        $this->_requirements[] = $pRequirement;
    }
    
    /**
     * @param PHPChecker_Requirement $pRequirement
     * @return boolean
     */
    public function remove(PHPChecker_Requirement $pRequirement)
    {
        $pos = array_search($pRequirement, $this->_requirements, true);
        
        if(false !== $pos)
        {
            array_splice($this->_requirements, $pos, 1);
            return true;
        }
        
        return false;
    }
    
    /**
     * @param array $pRequirements
     */
    public function sets(array $pRequirements)
    {
        $this->_requirements = array();
        
        foreach($pRequirements as $requirement)
        {
            $this->add($requirement);
        }
    }
    
    /**
     * @param boolean $pExecute
     * @return array
     */
    public function gets($pExecute = true)
    {
        if($pExecute)
        {
            $this->execute();
        }
        
        return $this->_requirements;
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        $result = array();
        
        foreach($this->_requirements as $requirement)
        {
            if(!$requirement->isOptional())
            {
                $result[] = $requirement;
            }
        }
        
        return $result;
    }
    
    /**
     * @return array
     */
    public function getRecommendations()
    {
        $result = array();
        
        foreach($this->_requirements as $requirement)
        {
            if($requirement->isOptional())
            {
                $result[] = $requirement;
            }
        }
        
        return $result;
    }
    
    /**
     * @return array
     */
    public function getSuccessRequirements()
    {
        $result = array();
        
        foreach($this->_requirements as $requirement)
        {
            if($requirement->isSuccess())
            {
                $result[] = $requirement;
            }
        }
        
        return $result;
    }
    
    /**
     * @return array
     */
    public function getFailedRequirements()
    {
        $result = array();
        
        foreach($this->_requirements as $requirement)
        {
            if($requirement->isFail())
            {
                $result[] = $requirement;
            }
        }
        
        return $result;
    }
    
    public function execute()
    {
        foreach($this->_requirements as $requirement)
        {
            $requirement->assert();
        }
    }
    
    public function getIterator()
    {
        return new ArrayIterator($this->_requirements);
    }
}