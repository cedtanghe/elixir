<?php

namespace Elixir\Config\Loader;

use Elixir\Config\Loader\LoaderAbstract;
use Elixir\Config\Loader\LoaderInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Arr extends LoaderAbstract 
{
    /**
     * @see LoaderInterface::load()
     */
    public function load($config, $recursive = false) 
    {
        if (!is_array($config)) 
        {
            $config = include $config;
        }

        $result = [];
        $supers = [];

        $m = $this->environment;

        if (null !== $m) 
        {
            $found = false;

            do 
            {
                foreach ($config as $key => $value)
                {
                    $k = explode('>', $key);

                    if (trim($k[0]) === $m) 
                    {
                        $found = true;
                        $supers[] = $value;

                        if (isset($k[1])) 
                        {
                            $m = trim($k[1]);
                            continue 2;
                        }
                    }
                }

                $m = null;
            } 
            while (null !== $m);

            if (!$found && !$this->strict) 
            {
                $supers[] = $config;
            }
        } 
        else 
        {
            $supers[] = $config;
        }

        foreach (array_reverse($supers) as $data)
        {
            $data = $this->parse($data, $recursive);
            $result = $recursive ? array_merge_recursive($result, $data) : array_merge($result, $data);
        }

        return $result;
    }

    /**
     * @see LoaderAbstract::parse();
     */
    protected function parse($data, $recursive)
    {
        foreach ($data as $key => &$value)
        {
            if (is_array($value)) 
            {
                $value = $this->parse($value, $recursive);
            }
        }

        return $data;
    }
}
