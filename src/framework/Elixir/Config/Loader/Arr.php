<?php

namespace Elixir\Config\Loader;

use Elixir\Config\Loader\LoaderAbstract;
use Elixir\Config\Loader\LoaderFactory;
use Elixir\Util\Arr as ArrayUtils;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Arr extends LoaderAbstract
{
    /**
     * @see LoaderInterface::load()
     * @throws \LogicException
     */
    public function load($pConfig, $pRecursive = false)
    {
        $dirname = '';
        
        if(!is_array($pConfig))
        {
            $dirname = dirname($pConfig);
            $pConfig = include $pConfig;
        }
        
        $result = [];
        $supers = [];
        
        $m = $this->_environment;
        
        if(null !== $m)
        {
            if(isset($pConfig['@include']))
            {
                throw new \LogicException('Include a first level is prohibited if an environment is defined.');
            }
            
            $found = false;
            
            do
            {
                foreach($pConfig as $key => $value)
                {
                    $k = explode(':', $key);

                    if(trim($k[0]) === $m)
                    {
                        $found = true;
                        $supers[] = $value;

                        if(isset($k[1]))
                        {
                            $m = trim($k[1]);
                            continue 2;
                        }
                    }
                }
                
                $m = null;
            }
            while(null !== $m);
            
            if(!$found && !$this->_strict)
            {
                $supers[] = $pConfig;
            }
        }
        else
        {
            $supers[] = $pConfig;
        }
        
        foreach(array_reverse($supers) as $data)
        {
            $data = $this->parse($data, $pRecursive, $dirname);
            $result = $pRecursive ? ArrayUtils::merge($result, $data) : array_merge($result, $data);
        }
        
        return $result;
    }
    
    /**
     * @see LoaderAbstract::parse();
     * @throws \LogicException
     */
    protected function parse($pData, $pRecursive = false, $pDirname = '')
    {
        $data = $pData;
        
        if(!is_array($data))
        {
            $pDirname = dirname($data);
            $data = include $pData;
        }
        
        $includes = [];
        
        foreach($data as $key => &$value)
        {  
            if($key === '@include')
            {
                $file = $pDirname . '/' . $value;
                $loader = LoaderFactory::create($file, ['environment' => $this->_environment, 'strict' => $this->_strict]);
                
                if(!$loader instanceof LoaderAbstract)
                {
                    throw new \LogicException('Loader must be "\Elixir\Config\Loader\LoaderAbstract" type to load external resources.');
                }
                
                $includes[] = $loader->load($file, $pRecursive);
                unset($data[$key]);
            }
            else if(is_array($value))
            {
                $value = $this->parse($value, $pRecursive, $pDirname);
            }
        }
        
        foreach($includes as $config)
        {
            $data = $pRecursive ? ArrayUtils::merge($data, $config) : array_merge($data, $config);
        }
        
        return $data;
    }
}