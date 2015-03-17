<?php

namespace Elixir\Dispatcher;

use Elixir\Dispatcher\DispatcherInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface SubscriberInterface 
{
    /**
     * @param DispatcherInterface $dispatcher
     */
    public function subscribe(DispatcherInterface $dispatcher);

    /**
     * @param DispatcherInterface $dispatcher
     */
    public function unsubscribe(DispatcherInterface $dispatcher);
}
