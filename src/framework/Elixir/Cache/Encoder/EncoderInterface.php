<?php

namespace Elixir\Cache\Encoder;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface EncoderInterface
{
    /**
     * @param mixed $pValue 
     * @return mixed
     */
    public function encode($pValue);
    
    /**
     * @param mixed $pValue 
     * @return mixed
     */
    public function decode($pValue);
}
