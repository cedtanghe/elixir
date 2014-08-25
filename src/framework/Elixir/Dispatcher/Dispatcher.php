<?php

namespace Elixir\Dispatcher;

use Elixir\Dispatcher\DispatcherInterface;
use Elixir\Dispatcher\Event;
use Elixir\Dispatcher\SubscriberInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Dispatcher implements DispatcherInterface
{
    /**
     * @var mixed 
     */
    protected $_target;
    
    /**
     * @var array 
     */
    protected $_listeners = [];
    
    /**
     * @var array 
     */
    protected $_serials = [];
    
    /**
     * @param mixed $pTarget 
     */
    public function __construct($pTarget = null) 
    {
        $this->setTarget($pTarget);
    }
    
    /**
     * @return mixed
     */
    public function getTarget()
    {
        if(null === $this->_target)
        {
            $this->_target = $this;
        }
        
        return $this->_target;
    }
    
    /**
     * @param mixed $pValue 
     */
    public function setTarget($pValue)
    {
        $this->_target = $pValue;
    }

    /**
     * @see DispatcherInterface::hasListener()
     */
    public function hasListener($pType)
    {
        return isset($this->_listeners[$pType]);
    }
    
    /**
     * @see DispatcherInterface::addListener()
     */
    public function addListener($pType, callable $pCallback, $pPriority = 0)
    {
        if($this->hasListener($pType))
        {
            foreach($this->_listeners[$pType] as $listener)
            {
                if($listener['callback'] === $pCallback)
                {
                    $listener['priority'] = (int)$pPriority;
                    $listener['serial'] = $this->_serials[$pType]++;
                    
                    return;
                }
            }
            
            $this->_listeners[$pType][] = [
                'callback' => $pCallback,
                'priority' => (int)$pPriority,
                'serial' => $this->_serials[$pType]++
            ];
        }
        else
        {
            $this->_serials[$pType] = 0;
            $this->_listeners[$pType] = [
                [
                    'callback' => $pCallback,
                    'priority' => (int)$pPriority,
                    'serial' => $this->_serials[$pType]++
                ]
            ];
        }
    }
    
    /**
     * @see DispatcherInterface::removeListener()
     */
    public function removeListener($pType, callable $pCallback)
    {
        if($this->hasListener($pType))
        {
            $i = count($this->_listeners[$pType]);
            
            while($i--)
            {
                $c = $this->_listeners[$pType][$i]['callback'];
                
                if($c === $pCallback)
                {
                    array_splice($this->_listeners[$pType], $i, 1);
                    break;
                }
            }
            
            if(empty($this->_listeners[$pType]))
            {
                unset($this->_listeners[$pType]);
            }
        }
    }
    
    /**
     * @see DispatcherInterface::getListeners()
     */
    public function getListeners()
    {
        return $this->_listeners;
    }
    
    /**
     * @see DispatcherInterface::removeListeners()
     */
    public function removeListeners()
    {
        $this->_listeners = [];
        $this->_serials = [];
    }
    
    /**
     * @see DispatcherInterface::addSubscriber()
     */
    public function addSubscriber(SubscriberInterface $pSubscriber)
    {
        $pSubscriber->subscribe($this);
    }
    
    /**
     * @see DispatcherInterface::removeSubscriber()
     */
    public function removeSubscriber(SubscriberInterface $pSubscriber)
    {
        $pSubscriber->unsubscribe($this);
    }
    
    /**
     * @param array $p1
     * @param array $p2
     * @return integer 
     */
    protected function compare(array $p1, array $p2)
    {
        if ($p1['priority'] == $p2['priority']) 
        {
            return ($p1['serial'] < $p2['serial']) ? -1 : 1;
        }
        
        return ($p1['priority'] > $p2['priority']) ? -1 : 1;
    }
    
    /**
     * @see DispatcherInterface::dispatch()
     */
    public function dispatch(Event $pEvent)
    {
        if($this->hasListener($pEvent->getType()))
        {
            $pEvent->setTarget($this->getTarget());
            
            $listeners = &$this->_listeners[$pEvent->getType()];
            usort($listeners, [$this, 'compare']);
            
            foreach($listeners as $listener)
            {
                call_user_func_array($listener['callback'], [$pEvent]);
                
                if($pEvent->isStopped())
                {
                    break;
                }
            }
        }
    }
}
