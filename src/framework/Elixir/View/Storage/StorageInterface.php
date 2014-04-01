<?php

namespace Elixir\View\Storage;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface StorageInterface
{
    /**
     * @return string
     */
    public function getContent();
}