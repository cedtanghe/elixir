<?php

namespace Elixir\Cache\Encoder;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface EncoderInterface
{
    /**
     * @param mixed $value 
     * @return mixed
     */
    public function encode($value);
    
    /**
     * @param mixed $value 
     * @return mixed
     */
    public function decode($value);
}
