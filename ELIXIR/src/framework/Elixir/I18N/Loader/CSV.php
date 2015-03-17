<?php

namespace Elixir\I18N\Loader;

use Elixir\I18N\Loader\LoaderInterface;
use Elixir\Util\CSV as CSVUtils;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class CSV implements LoaderInterface
{
    /**
     * @see LoaderInterface::load()
     */
    public function load($pResource)
    {
        $data = [];
        $CSV = CSVUtils::CSVToArray($pResource);
        
        foreach($CSV as $line)
        {
            $data[$line[0]] = isset($line[1]) ? $line[1] : '';
        }
        
        return $data;
    }
}
