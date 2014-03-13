<?php

namespace Elixir\Config\Loader;

use Elixir\Util\Arr as ArrayUtils;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class XML extends LoaderAbstract
{
    /**
     * @see LoaderInterface::load()
     * @throws \LogicException
     */
    public function load($pConfig, $pRecursive = false)
    {
        $dirname = '';
        
        if(is_file($pConfig))
        {
            $dirname = dirname($pConfig);
            $pConfig = simplexml_load_file($pConfig);
        }
        
        $result = array();
        $supers = array();
        
        $m = $this->_environment;
        
        if(null !== $m)
        {
            if(isset($pConfig->include) && isset($pConfig->include['href']))
            {
                throw new \LogicException('Include a first level is prohibited if an environment is defined.');
            }
            
            if(!$this->_strict && !isset($pConfig->{$m}))
            {
                $supers[] = $pConfig;
            }
            else
            {
                do
                {
                    $item = isset($pConfig->{$m}) ? $pConfig->{$m} : null;

                    if (null !== $item)
                    {
                        $supers[] = $item;
                        $m = isset($item['extends']) ? $item['extends'] : null;
                    }
                    else
                    {
                        $m = null;
                    }
                }
                while (null !== $m);
            }
        }
        else
        {
            $supers[] = $pConfig;
        }
        
        foreach(array_reverse($supers) as $item)
        {
            $data = (array)$this->parse($item, $pRecursive, $dirname);
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
        
        if(is_file($data))
        {
            $pDirname = dirname($data);
            $data = simplexml_load_file($data);
        }
        
        if(count($data->children()) == 0)
        {
            return (string)$data;
        }
        else
        {
            $includes = array();
            $r = array();
            
            foreach($data->children() as $key => $value)
            {
                if($key === 'include' && isset($data->{$key}['href']))
                {
                    $file = $pDirname . '/' . $data->{$key}['href'];
                    $loader = LoaderFactory::create($file, array('environment' => $this->_environment, 'strict' => $this->_strict));
                
                    if(!$loader instanceof LoaderAbstract)
                    {
                        throw new \LogicException('Loader must be "Elixir\Config\Loader\LoaderAbstract" type to load external resources');
                    }

                    $includes[] = $loader->load($file, $pRecursive);
                }
                else if(isset($r[$key]))
                {
                    $r[$key] = (array)$r[$key];
                    $r[$key][] = $this->parse($value, $pRecursive, $pDirname);
                }
                else 
                {
                    $r[$key] = $this->parse($value, $pRecursive, $pDirname);
                }
            }
            
            foreach($includes as $config)
            {
                $r = $pRecursive ? ArrayUtils::merge($r, $config) : array_merge($r, $config);
            }
            
            return $r;
        }
    }
}