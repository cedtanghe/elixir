<?php

namespace Elixir\DB\ORM\Model;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class ModelEvent extends Event
{
    /**
     * @var string
     */
    const DEFINE_COLUMNS = 'define_columns';
    
    /**
     * @var string
     */
    const DEFINE_GUARDED = 'define_guarded';
}
