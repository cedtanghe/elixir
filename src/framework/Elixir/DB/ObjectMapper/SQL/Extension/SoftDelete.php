<?php

namespace Elixir\DB\ObjectMapper\SQL\Extension;

use Elixir\DB\ObjectMapper\FindableExtensionInterface;
use Elixir\DB\ObjectMapper\FindableInterface;
use Elixir\DB\ObjectMapper\RepositoryInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class SoftDelete implements FindableExtensionInterface 
{
    /**
     * @var FindableInterface 
     */
    protected $findable;
    
    /**
     * @var RepositoryInterface 
     */
    protected $repository;
    
    /**
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @see FindableExtensionInterface::setFindable()
     */
    public function setFindable(FindableInterface $value) 
    {
        $this->findable = $value;
    }
    
    /**
     * @return void
     */
    public function withTrashed()
    {
        // Todo
    }
    
    /**
     * @return void
     */
    public function onlyTrashed()
    {
        // Todo
    }
    
    /**
     * @param integer|string|\DateTime $date
     */
    public function trashedBefore($date)
    {
        $date = $this->convertDate($date);
        
        // Todo
    }
    
    /**
     * @param integer|string|\DateTime $date
     */
    public function trashedAfter($date)
    {
        $date = $this->convertDate($date);
        
        // Todo
    }
    
    /**
     * @param integer|string|\DateTime $start
     * @param integer|string|\DateTime $end
     */
    public function trashedBetween($start, $end)
    {
        $start = $this->convertDate($start);
        $end = $this->convertDate($end);
        
        // Todo
    }
    
    /**
     * @param integer|string|\DateTime $date
     * @return integer
     */
    protected function convertDate($date)
    {
        if ($date instanceof \DateTime)
        {
            $timestamp = $ttl->getTimestamp();
            return date($this->repository->getDeletedFormat(), $timestamp);
        }
        else if (is_numeric($date))
        {
            $timestamp = strtotime($date);
            return date($this->repository->getDeletedFormat(), $timestamp);
        }
        
        return $date;
    }

    /**
     * @see FindableExtensionInterface::getRegisteredMethods()
     */
    public function getRegisteredMethods() 
    {
        return [
            'withTrashed',
            'onlyTrashed',
            'trashedBefore',
            'trashedAfter',
            'trashedBetween'
        ];
    }
}
