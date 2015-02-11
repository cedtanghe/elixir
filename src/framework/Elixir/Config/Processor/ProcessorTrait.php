<?php

namespace Elixir\Config\Processor;

use Elixir\Config\Processor\ProcessorInterface;

trait ProcessorTrait 
{
    /**
     * @var array
     */
    protected $processors = [];
    
    /**
     * @param ProcessorInterface $processor
     */
    public function addProcessor(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * @return array
     */
    public function getProcessors() 
    {
        return $this->processors;
    }
    
    /**
     * @param mixed $value
     * @return mixed;
     */
    protected function process($value) 
    {
        foreach ($this->processors as $processor) 
        {
            $value = $processor->process($value);
        }

        return $value;
    }
}
