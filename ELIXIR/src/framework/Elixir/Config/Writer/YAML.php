<?php

namespace Elixir\Config\Writer;

use Elixir\Config\Writer\WriterAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class YAML extends WriterAbstract 
{
    /**
     * @var callable 
     */
    protected $YAMLEncoder;

    /**
     * @see WriterAbstract::__construct()
     * @param callable $YAMLEncoder
     */
    public function __construct(ConfigInterface $config = null, callable $YAMLEncoder = null)
    {
        parent::__construct($config);

        if (null !== $YAMLEncoder)
        {
            $this->setYAMLEncoder($YAMLEncoder);
        } 
        else 
        {
            if (function_exists('yaml_emit')) 
            {
                $this->setYAMLEncoder('yaml_emit');
            }
        }
    }
    
    /**
     * @param callable $value
     */
    public function setYAMLEncoder(callable $value)
    {
        $this->YAMLEncoder = $value;
    }
    
    /**
     * @return callable
     */
    public function getYAMLEncoder()
    {
        return $this->YAMLEncoder;
    }

    /**
     * @see WriterAbstract::write()
     */
    public function write() 
    {
        return call_user_func($this->getYAMLEncoder(), $this->config);
    }

    /**
     * @see WriterAbstract::export()
     */
    public function export($file)
    {
        if(substr($file, -4) != '.yml')
        {
            $file .= '.yml';
        }
        
        file_put_contents($file, $this->write());
        return file_exists($file);
    }
}
