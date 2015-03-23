<?php

namespace Elixir\DB\ObjectMapper;

use Elixir\DB\ObjectMapper\FindableInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface FindableExtensionInterface
{
    /**
     * @param FindableInterface $findable
     */
    public function setFindable(FindableInterface $findable);
}
