<?php

namespace Elixir\Filter;

use Elixir\Filter\FilterAbstract;
use Elixir\Filter\FilterInterface;
use Elixir\Util\Str;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Protect extends FilterAbstract
{
    /**
     * @see FilterInterface::filter()
     */
    public function filter($pContent, array $pOptions = array())
    {
        return preg_replace('/[^a-z0-9\._\-\(\)]+/i', '', Str::removeAccents($pContent));
    }
}
