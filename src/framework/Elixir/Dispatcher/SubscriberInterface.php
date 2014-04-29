<?php

namespace Elixir\Dispatcher;

use Elixir\Dispatcher\DispatcherInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface SubscriberInterface 
{
    /**
     * @param Dispatcher $pDispatcher
     */
    public function subscribe(DispatcherInterface $pDispatcher);
    
    /**
     * @param Dispatcher $pDispatcher
     */
    public function unsubscribe(DispatcherInterface $pDispatcher);
}
