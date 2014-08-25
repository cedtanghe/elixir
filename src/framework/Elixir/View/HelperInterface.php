<?php

namespace Elixir\View;

use Elixir\DI\ContainerInterface;
use Elixir\View\Helper\Container;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface HelperInterface
{
    /**
     * @param Container|ContainerInterface $pValue
     */
    public function setHelperContainer($pValue);
    
    /**
     * @return Container
     */
    public function getHelperContainer();
    
    /**
     * @param string $pKey
     * @return mixed
     */
    public function helper($pKey);
}
