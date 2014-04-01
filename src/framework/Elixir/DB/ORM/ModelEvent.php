<?php

namespace Elixir\DB\ORM;

use Elixir\Dispatcher\Event;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class ModelEvent extends Event
{
    /**
     * @var string
     */
    const PRE_UPDATE = 'pre_update';
    
    /**
     * @var string
     */
    const PRE_INSERT = 'pre_insert';
    
    /**
     * @var string
     */
    const PRE_DELETE = 'pre_delete';
    
    /**
     * @var string
     */
    const INSERT = 'insert';
    
    /**
     * @var string
     */
    const DELETE = 'delete';
}
