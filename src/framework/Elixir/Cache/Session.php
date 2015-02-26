<?php

namespace Elixir\Cache;

use Elixir\Cache\CacheAbstract;
use Elixir\HTTP\Session\Session as SessionData;
use Elixir\HTTP\Session\SessionInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Session extends CacheAbstract
{
    /**
     * @var Elixir\HTTP\Session\SessionInterface
     */
    protected $session;
    
    /**
     * @see CacheAbstract::__construct()
     * @param SessionInterface $session
     */
    public function __construct($identifier, SessionInterface $session = null) 
    {
        parent::__construct($identifier);
        $this->session = $session ?: SessionData::instance();
    }

    /**
     * @see CacheAbstract::has()
     */
    public function has($key)
    {
        $data = $this->session->get([$this->identifier, $key], null);
        
        if (null !== $data)
        {
            $expired = time() > (int)$data['TTL'];
            
            if ($expired)
            {
                $this->session->remove([$this->identifier, $key]);
            }
            
            return !$expired;
        }
        
        return false;
    }
    
    /**
     * @see CacheAbstract::get()
     */
    public function get($key, $default = null)
    {
        $data = $this->session->get([$this->identifier, $key], null);

        if (null !== $data)
        {
            $expired = time() > (int)$data['TTL'];

            if ($expired) 
            {
                $this->session->remove([$this->identifier, $key]);
                return is_callable($default) ? call_user_func($default) : $default;
            }
            
            return $this->getEncoder()->decode($data['value']);
        }

        return is_callable($default) ? call_user_func($default) : $default;
    }
    
    /**
     * @see CacheAbstract::set()
     */
    public function set($key, $value, $TTL = 0)
    {
        $this->session->set(
            [$this->identifier, $key], 
            [
                'value' => $this->getEncoder()->encode($value),
                'TTL' => time() + $this->convertTTL($TTL)
            ]
        );
    }
    
    /**
     * @see CacheAbstract::remove()
     */
    public function remove($key)
    {
        $this->session->remove([$this->identifier, $key]);
    }
    
    /**
     * @see CacheAbstract::has()
     */
    public function clear()
    {
        $this->session->remove($this->identifier);
    }
}
