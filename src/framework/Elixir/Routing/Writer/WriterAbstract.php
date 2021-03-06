<?php

namespace Elixir\Routing\Writer;

use Elixir\Routing\RouterInterface;
use Elixir\Routing\Writer\WriterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class WriterAbstract implements WriterInterface
{
    /**
     * @var RouterInterface
     */
    protected $_router;
    
    /**
     * @param RouterInterface $pRouter
     */
    public function __construct(RouterInterface $pRouter = null)
    {
        $this->_router = $pRouter;
    }

    /**
     * @param RouterInterface $pValue
     */
    public function setRouter(RouterInterface $pValue)
    {
        $this->_router = $pValue;
    }
    
    /**
     * @return RouterInterface
     */
    public function getRouter()
    {
        return $this->_router;
    }
}
