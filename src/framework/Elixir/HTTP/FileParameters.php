<?php

namespace Elixir\HTTP;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class FileParameters extends Parameters
{
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @return Uploader|mixed
     */
    public function getUploaderFile($pKey, $pDefault = null) 
    {
        if($this->has($pKey))
        {
            return new Uploader($pKey);
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
}