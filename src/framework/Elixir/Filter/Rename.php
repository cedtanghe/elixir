<?php

namespace Elixir\Filter;

use Elixir\Filter\FilterAbstract;
use Elixir\Filter\FilterInterface;
use Elixir\Util\File;
use Elixir\Util\Str;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Rename extends FilterAbstract
{
    /**
     * @var string
     */
    const APPEND = 'append';
    
    /**
     * @var string
     */
    const PREPEND = 'prepend';
    
    /**
     * @var string
     */
    const SET = 'set';
    
    /**
     * @see FilterInterface::filter()
     * @throws \RuntimeException
     */
    public function filter($pContent, array $pOptions = array())
    {
        $pOptions = array_merge($this->_options, $pOptions);
        
        if(is_array($pContent))
        {
            $file = $pContent['name'];
            $process = false;
        }
        else
        {
            $file = $pContent;
            $old = $file;
            $process = true;
        }
        
        $folder = rtrim(isset($pOptions['folder']) ? $pOptions['folder'] : File::dirname($file), '/');
        
        if(isset($pOptions['name']))
        {
            $mode = isset($pOptions['mode']) ? $pOptions['mode'] : self::SET;
            
            switch($mode)
            {
                case self::APPEND:
                    $fileName = File::filename($file) . $pOptions['name'];
                break;
                case self::PREPEND:
                    $fileName = $pOptions['name'] . File::filename($file);
                break;
                default:
                    $fileName = $pOptions['name'];
                break;
            }
        }
        else
        {
            $fileName = File::filename($file);
        }
        
        if(isset($pOptions['protect']) && $pOptions['protect'])
        {
            $fileName = preg_replace('/[^a-z0-9\._\-\(\)]+/i', '', Str::removeAccents($fileName));
        }
        
        $extension = isset($pOptions['extension']) ? $pOptions['extension'] : File::extension($file);
        $file = $folder . '/' . $fileName;
        
        if(!empty($extension))
        {
            $file .= '.' . $extension;
        }
        
        if(isset($pOptions['override']) && !$pOptions['override'])
        {
            $c = 0;
            
            while(file_exists($file))
            {
                if(preg_match('/^(.+)-copy\(' . $c . '\)$/', $fileName, $matches))
                {
                    $fileName = $matches[1] . '-copy(' . ++$c . ')';
                }
                else
                {
                    $fileName .= '-copy(0)';
                }
                
                $file = $folder . '/' . $fileName;
                
                if(!empty($extension))
                {
                    $file .= '.' . $extension;
                }
            }
        }
        
        if(!is_dir(dirname($file)))
        {
            @mkdir(dirname($file), 0777, true);
        }
        
        if($process)
        {
            if(false === @rename($old, $file))
            {
                throw new \RuntimeException('The filter "\Elixir\Filter\Rename" was not performed.');
            }
            
            return $file;
        }
        else
        {
            $pContent['name'] = $file;
            return $pContent;
        }
    }
}