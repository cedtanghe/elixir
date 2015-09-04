<?php

namespace Elixir\Dispatcher;

use Elixir\Dispatcher\SubscriberInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
interface DispatcherInterface 
{
    /**
     * @param SubscriberInterface $subscriber
     */
    public function addSubscriber(SubscriberInterface $subscriber);

    /**
     * @param SubscriberInterface $subscriber
     */
    public function removeSubscriber(SubscriberInterface $subscriber);
    
    /**
     * @param string $type 
     * @return boolean
     */
    public function hasListener($type);
    
    /**
     * @param string $type
     * @param callable $callback
     * @param integer $priority 
     */
    public function addListener($type, callable $callback, $priority = 0);
    
    /**
     * @param string $type
     * @param callable $callback 
     */
    public function removeListener($type, callable $callback = null);

    /**
     * @return boolean
     */
    public function hasListeners();
    
    /**
     * @return array
     */
    public function getListeners();
    
    /**
     * @return void
     */
    public function removeListeners();
    
    /**
     * @param Event $event 
     */
    public function dispatch(Event $event);
}
