<?php

namespace Elixir\Routing\Generator;

use Elixir\HTTP\Request;
use Elixir\Routing\Route;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface GeneratorInterface
{
    /**
     * @var string
     */
    const SHEMA_RELATIVE = 'shema_relative';
    
    /**
     * @var string
     */
    const URL_RELATIVE = 'url_relative';
    
    /**
     * @var string
     */
    const URL_ABSOLUTE = 'url_absolute';
    
    /**
     * @param Request $pValue
     */
    public function setRequest(Request $pValue);
    
    /**
     * @param Route $pRoute
     * @param array $pOptions
     * @param string $pMode
     * @return string
     */
    public function generate(Route $pRoute, array $pOptions = array(), $pMode = self::URL_RELATIVE);
}
