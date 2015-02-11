<?php

namespace Elixir\Config\Writer;

use Elixir\Config\ConfigInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface WriterInterface
{
    /**
     * @param ConfigInterface $value
     */
    public function setConfig(ConfigInterface $value);

    /**
     * @return mixed
     */
    public function write();

    /**
     * @param string $file
     * @return boolean
     */
    public function export($file);
}
