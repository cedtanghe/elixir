<?php

namespace Elixir\Routing\Matcher;

use Elixir\HTTP\Request;
use Elixir\Routing\Collection;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface MatcherInterface
{
    /**
     * @param Request $pValue
     */
    public function setRequest(Request $pValue);
    
    /**
     * @param Collection $pCollection
     * @param string $pURL
     * @return RouteMatch|null
     */
    public function match(Collection $pCollection, $pURL);
}
