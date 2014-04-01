<?php

namespace Elixir\Filter;

use Elixir\Util\File;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Duplicate extends Chain
{
    /**
     * @see FilterInterface::filter()
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function filter($pContent, array $pOptions = array())
    {
        $pOptions = array_merge($this->_options, $pOptions);
        
        if(is_array($pContent))
        {
            $upload = true;
            $current = $pContent['tmp_name'];
            $extension = File::extension($pContent['name']);
        }
        else
        {
            $upload = false;
            $current = $pContent;
            $extension = File::extension($current);
        }
        
        $file = $current;
        $folder = rtrim(File::dirname($file), '/');
        $fileName = File::filename($file);
        $c = 0;
        
        while(file_exists($file))
        {
            if(preg_match('/^(.+)-copy\(' . $c . '\)$/', $file, $matches))
            {
                $fileName = $matches[1] . '-copy(' . ++$c . ')';
            }
            else
            {
                $fileName .= '-copy(0)';
            }
            
            $file = $folder . '/' . $fileName . '.' . $extension;
        }
        
        if(!File::copy($current, $file))
        {
            throw new \RuntimeException('The filter "\Elixir\Filter\Duplicate" was not performed.');
        }
        
        try
        {
            $file = parent::filter($file, $pOptions);
            return (!$upload || (isset($pOptions['file']) && $pOptions['file'])) ? $file : $pContent;
        }
        catch(\Exception $e)
        {
            unset($file);
            throw $e;
        }
    }
}