<?php

namespace Elixir\HTTP;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
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
        
        if(is_callable($pDefault))
        {
            return call_user_func($pDefault);
        }
        
        return $pDefault;
    }
}