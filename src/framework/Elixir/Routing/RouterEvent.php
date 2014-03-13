<?php

namespace Elixir\Routing;

use Elixir\Dispatcher\Event;
use Elixir\Routing\Matcher\RouteMatch;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class RouterEvent extends Event
{
    /**
     * @var string
     */
    const ROUTE_MATCH = 'route_match';
    
    /**
     * @var string
     */
    const ROUTE_NOT_FOUND = 'route_not_found';

    /**
     * @var RouteMatch 
     */
    protected $_routeMatch;
    
    /**
     * @see Event::__contruct()
     * @param RouteMatch $pRouteMatch
     */
    public function __construct($pType, RouteMatch $pRouteMatch = null) 
    {
        $this->_type = $pType;
        $this->_routeMatch = $pRouteMatch;
    }
    
    /**
     * @return RouteMatch
     */
    public function getRouteMatch()
    {
        return $this->_routeMatch;
    }
}
