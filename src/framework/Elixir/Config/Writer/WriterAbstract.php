<?php

namespace Elixir\Config\Writer;

use Elixir\Config\ConfigInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

abstract class WriterAbstract implements WriterInterface
{
    /**
     * @var ConfigInterface
     */
    protected $_config;
    
    /**
     * @param ConfigInterface $pConfig
     */
    public function __construct(ConfigInterface $pConfig = null)
    {
        $this->_config = $pConfig;
    }

    /**
     * @param ConfigInterface $pValue
     */
    public function setConfig(ConfigInterface $pValue)
    {
        $this->_config = $pValue;
    }
    
    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->_config;
    }
}