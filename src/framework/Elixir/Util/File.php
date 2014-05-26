<?php

namespace Elixir\Util;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class File
{
    /**
     * @param string $pFilePath
     * @return string
     */
    public static function extension($pFilePath)
    {
        return pathinfo($pFilePath, PATHINFO_EXTENSION);
    }
    
    /**
     * @param string $pFilePath
     * @return string
     */
    public static function dirname($pFilePath)
    {
        return pathinfo($pFilePath, PATHINFO_DIRNAME);
    }
    
    /**
     * @param string $pFilePath
     * @return string
     */
    public static function basename($pFilePath)
    {
        return pathinfo($pFilePath, PATHINFO_BASENAME);
    }
    
    /**
     * @param string $pFilePath
     * @return string
     */
    public static function filename($pFilePath)
    {
        return pathinfo($pFilePath, PATHINFO_FILENAME);
    }
    
    /**
     * @param string $pFilePath
     * @return string
     */
    public static function mimeType($pFilePath)
    {
        if(extension_loaded('fileinfo'))
        {
            $finfo = finfo_open(FILEINFO_MIME);
            $filemime = finfo_file($finfo, realpath($pFilePath));
            finfo_close($finfo);
        }
        else if(function_exists('mime_content_type'))
        {
            $filemime = mime_content_type($pFilePath);
        }
        else
        {
            $filemime = exec('file -bi "' . $pFilePath . '"');
        }

        if(isset($filemime))
        {
            $idx = strpos($filemime, ';');

            if($idx !== false)
            {
                $filemime = substr($filemime, 0, $idx);
            }

            return $filemime;
        }
    }
    
    /**
     * @param string $pSrcPath
     * @param string $pDstPath
     * @param boolean $pOverride
     * @return boolean
     */
    public static function copy($pSrcPath, $pDstPath, $pOverride = true)
    {
        if(is_file($pSrcPath))
        {
            if(!$pOverride && is_file($pDstPath))
            {
                if(filemtime($pSrcPath) <= filemtime($pDstPath))
                {
                    return true;
                }
            }
            
            if(!file_exists(dirname($pDstPath)))
            {
                @mkdir(dirname($pDstPath), 0777, true);
            }
            
            $src = fopen($pSrcPath, 'r');
            $dest = fopen($pDstPath, 'w+'); 
            
            stream_copy_to_stream($src, $dest); 
            fclose($src); 
            fclose($dest);
            
            if(!is_file($pDstPath))
            {
                return false;
            }

            return true;
        }
        else if(is_dir($pSrcPath))
        {
            if(!file_exists($pDstPath))
            {
                @mkdir($pDstPath, 0777, true);
            }
		
            $pSrcPath = realpath($pSrcPath);
            $pDstPath = realpath($pDstPath);
            
            $iterator = new \RecursiveDirectoryIterator($pSrcPath, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS);
            $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);

            foreach($iterator as $fileinfo)
            {
                if(!static::copy($fileinfo->getPathname(),
                            str_replace($pSrcPath, $pDstPath, $fileinfo->getPathname()),
                            $pOverride))
                {
                        return false;
                }
            }
			
            return true;
        }
        
        return false;
    }
    
    /**
     * @param string $pPath
     * @return array
     */
    public static function filesList($pPath)
    {
        $list = [];
        
        if(is_file($pPath))
        {
            $list[] = $pPath;
        }
        else if(is_dir($pPath))
        {
            $iterator = new \RecursiveDirectoryIterator($pPath, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS);
            $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);

            foreach($iterator as $fileinfo)
            {
                $list = array_merge($list, static::filesList($fileinfo->getPathname()));
            }
        }
        
        return $list;
    }
    
    /**
     * @param string $pPath
     * @return boolean
     */
    public static function remove($pPath)
    {
        if(is_dir($pPath))
        {
            $iterator = new \FilesystemIterator($pPath);
            
            foreach($iterator as $fileinfo) 
            {
                if($fileinfo->isFile() || $fileinfo->isLink()) 
                {
                    @unlink($fileinfo->getPathName());
                } 
                else if($fileinfo->isDir()) 
                {
                    static::remove($fileinfo->getPathName());
                }
            }
            
            return @rmdir($pPath);
        }
        else
        {
            return @unlink($pPath);
        }
    }
    
    /**
     * @param string $pOldName
     * @param string $pNewName
     * @return boolean
     */
    public static function rename($pOldName, $pNewName)
    {
        if($pOldName == $pNewName)
        {
            return true;
        }
        
        if(file_exists($pOldName))
        {
            if(is_dir($pOldName) && !file_exists($pNewName))
            {
                @mkdir($pNewName, 0777, true);
            }

            return rename($pOldName, $pNewName);
        }
        
        return false;
    }
}