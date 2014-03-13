<?php

namespace Elixir\View\Storage;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface StorageInterface
{
    /**
     * @return string
     */
    public function getContent();
}