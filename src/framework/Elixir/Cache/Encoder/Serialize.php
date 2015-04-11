<?php

namespace Elixir\Cache\Encoder;

use Elixir\Cache\Encoder\EncoderInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Serialize implements EncoderInterface
{
    /**
     * @see EncoderInterface::encode()
     */
    public function encode($value)
    {
        return serialize($value);
    }
    
    /**
     * @see EncoderInterface::decode()
     */
    public function decode($value)
    {
        return unserialize($value);
    }
}
