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
    public function encode($pValue)
    {
        return json_encode($pValue);
    }
    
    /**
     * @see EncoderInterface::decode()
     */
    public function decode($pValue)
    {
        return json_decode($pValue, true);
    }
}
