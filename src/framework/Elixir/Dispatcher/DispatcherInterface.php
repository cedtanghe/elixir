<?php

namespace Elixir\Dispatcher;

use Elixir\Dispatcher\SubscriberInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface DispatcherInterface 
{
    /**
     * @param string $pType 
     */
    public function hasListener($pType);
    
    /**
     * @param string $pType
     * @param callable $pCallback
     * @param integer $pPriority 
     */
    public function addListener($pType, $pCallback, $pPriority = 0);
    
    /**
     * @return array
     */
    public function getListeners();

    /**
     * @param string $pType
     * @param callable $pCallback 
     */
    public function removeListener($pType, $pCallback);
    
    public function removeListeners();
    
    /**
     * @param SubscriberInterface $pSubscriber
     */
    public function addSubscriber(SubscriberInterface $pSubscriber);
    
    /**
     * @param SubscriberInterface $pSubscriber
     */
    public function removeSubscriber(SubscriberInterface $pSubscriber);
    
    /**
     * @param Event $pEvent 
     */
    public function dispatch(Event $pEvent);
}
