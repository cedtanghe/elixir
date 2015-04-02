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
     * @var SessionInterface
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
     * @see CacheAbstract::exists()
     */
    public function exists($key)
    {
        return null !== $this->get($key, null);
    }
    
    /**
     * @see CacheAbstract::get()
     */
    public function get($key, $default = null)
    {
        $data = $this->session->get([$this->identifier, $key], null);

        if (null !== $data)
        {
            $expired = time() > (int)$data['ttl'];

            if ($expired) 
            {
                $this->session->remove([$this->identifier, $key]);
                return is_callable($default) ? call_user_func($default) : $default;
            }
            
            if (null !== $this->encoder)
            {
                $data['value'] = $this->getEncoder()->encode($data['value']);
            }
            
            return $data['value'];
        }

        return is_callable($default) ? call_user_func($default) : $default;
    }
    
    /**
     * @see CacheAbstract::store()
     */
    public function store($key, $value, $ttl = self::DEFAULT_TTL)
    {
        if (null !== $this->encoder)
        {
            $value = $this->getEncoder()->encode($value);
        }
        
        $this->session->set(
            [$this->identifier, $key], 
            [
                'value' => $value,
                'ttl' => time() + $this->parseTimeToLive($ttl)
            ]
        );
        
        return true;
    }
    
    /**
     * @see CacheAbstract::delete()
     */
    public function delete($key)
    {
        $this->session->remove([$this->identifier, $key]);
        return true;
    }
    
    /**
     * @see CacheAbstract::incremente()
     */
    public function incremente($key, $step = 1) 
    {
        $data = $this->session->get([$this->identifier, $key], null);

        if (null !== $data)
        {
            $expired = time() > (int)$data['ttl'];

            if ($expired) 
            {
                $this->session->remove([$this->identifier, $key]);
                return 0;
            }
            
            if (null !== $this->encoder)
            {
                $data['value'] = $this->getEncoder()->encode($data['value']);
            }
            
            $data['value'] += $step;
            $this->session->set([$this->identifier, $key], $data);
            
            return $data['value'];
        }
        
        return 0;
    }

    /**
     * @see CacheAbstract::decremente()
     */
    public function decremente($key, $step = 1) 
    {
        $data = $this->session->get([$this->identifier, $key], null);

        if (null !== $data)
        {
            $expired = time() > (int)$data['ttl'];

            if ($expired) 
            {
                $this->session->remove([$this->identifier, $key]);
                return 0;
            }
            
            if (null !== $this->encoder)
            {
                $data['value'] = $this->getEncoder()->encode($data['value']);
            }
            
            $data['value'] -= $step;
            $this->session->set([$this->identifier, $key], $data);
            
            return $data['value'];
        }
        
        return 0;
    }
    
    /**
     * @see CacheAbstract::flush()
     */
    public function flush()
    {
        $this->session->remove($this->identifier);
        return true;
    }
}
