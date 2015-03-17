<?php

namespace Elixir\Config\Writer;

use Elixir\Config\ConfigInterface;
use Elixir\Config\Writer\WriterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
abstract class WriterAbstract implements WriterInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config = null)
    {
        $this->config = $config;
    }

    /**
     * @see WriterInterface::setConfig()
     */
    public function setConfig(ConfigInterface $value)
    {
        $this->config = $value;
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig() 
    {
        return $this->config;
    }
}
