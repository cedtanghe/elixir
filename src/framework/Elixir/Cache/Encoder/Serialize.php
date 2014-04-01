<?php

namespace Elixir\Cache\Encoder;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Serialize implements EncoderInterface
{
    /**
     * @see EncoderInterface::encode()
     */
    public function encode($pValue)
    {
        return serialize($pValue);
    }
    
    /**
     * @see EncoderInterface::decode()
     */
    public function decode($pValue)
    {
        return unserialize($pValue);
    }
}
