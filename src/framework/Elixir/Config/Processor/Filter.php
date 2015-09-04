<?php

namespace Elixir\Config\Processor;

use Elixir\Config\Processor\ProcessorInterface;
use Elixir\Filter\FilterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Filter implements ProcessorInterface
{
    /**
     * @var FilterInterface 
     */
    protected $filter;

    /**
     * @var array 
     */
    protected $options = [];

    /**
     * @param FilterInterface $filter
     * @param array $options
     */
    public function __construct(FilterInterface $filter, array $options = []) 
    {
        $this->filter = $filter;
        $this->options = $options;
    }

    /**
     * @see ProcessorInterface::process()
     */
    public function process($value)
    {
        if (is_array($value) || is_object($value) || $value instanceof \Traversable) 
        {
            foreach ($value as &$value) 
            {
                $value = $this->process($value);
            }
        } 
        else 
        {
            $value = $this->filter->filter($value, $this->options);
        }

        return $value;
    }
}
