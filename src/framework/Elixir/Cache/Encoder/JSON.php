<?php

namespace Elixir\Cache\Encoder;

use Elixir\Cache\Encoder\EncoderInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class JSON implements EncoderInterface
{
    /**
     * @see EncoderInterface::encode()
     */
    public function encode($value)
    {
        return json_encode($value, JSON_PRETTY_PRINT);
    }
    
    /**
     * @see EncoderInterface::decode()
     */
    public function decode($value)
    {
        return json_decode($value, true);
    }
}
