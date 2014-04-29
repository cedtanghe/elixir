<?php

namespace Elixir\Routing;

use Elixir\Dispatcher\DispatcherInterface;
use Elixir\HTTP\Request;
use Elixir\Routing\Collection;
use Elixir\Routing\Generator\GeneratorInterface;
use Elixir\Routing\Matcher\RouteMatch;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface RouterInterface extends DispatcherInterface
{
    /**
     * @return Collection
     */
    public function getCollection();
    
    /**
     * @return Request
     */
    public function getRequest();
    
    /**
     * @param string $pURL
     * @return RouteMatch|null
     */
    public function match($pURL = null);
    
    /**
     * @param string $pName
     * @param array $pOptions
     * @param string $pMode
     * @return string
     */
    public function generate($pName, array $pOptions = array(), $pMode = GeneratorInterface::URL_ABSOLUTE);
}
