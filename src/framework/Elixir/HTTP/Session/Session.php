<?php

namespace Elixir\HTTP\Session;

use Elixir\Dispatcher\Dispatcher;
use Elixir\HTTP\Parameters;
use Elixir\HTTP\Session\SaveHandler\SaveHandlerInterface;
use Elixir\HTTP\Session\SessionEvent;
use Elixir\HTTP\Session\SessionInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Session extends Dispatcher implements SessionInterface, \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var string
     */
    const FLASH_KEY = '___SESSION_FLASH___';
    
    /**
     * @var Session
     */
    protected static $_instance;
    
    /**
     * @var SaveHandlerInterface
     */
    protected $_saveHandler;
    
    /**
     * @var Parameters
     */
    protected $_parameters;

    /**
     * @throws \LogicException
     */
    public function __construct() 
    {
        if(null !== static::$_instance)
        {
            throw new \LogicException('A session can have only one instance.');
        }
        
        static::$_instance = $this;
    }
    
    /**
     * @return Session|null
     */
    public static function instance()
    {
        return static::$_instance;
    }
    
    /**
     * @see SessionInterface::setSaveHandler()
     * @throws \LogicException
     */
    public function setSaveHandler(SaveHandlerInterface $pValue)
    {
        if($this->exist())
        {
            throw new \LogicException('Cannot set session handler after a session has already started.');
        }
        
        $this->_saveHandler = $pValue;
    }

    /**
     * @see SessionInterface::getSaveHandler()
     */
    public function getSaveHandler()
    {
        return $this->_saveHandler;
    }

    /**
     * @see SessionInterface::exist()
     */
    public function exist()
    {
        $sid = defined('SID') ? constant('SID') : false;
        
        if (false !== $sid && $this->getId())
        {
            return true;
        }
        
        if (headers_sent()) 
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * @see SessionInterface::setId()
     * @throws \LogicException
     */
    public function setId($pValue)
    {
        if($this->exist())
        {
            throw new \LogicException('Cannot set session id after a session has already started.');
        }
        
        session_id($pValue);
    }
    
    /**
     * @see SessionInterface::getId()
     */
    public function getId()
    {
        return session_id();
    }
    
    /**
     * @see SessionInterface::setName()
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function setName($pValue)
    {
        if($this->exist())
        {
            throw new \LogicException('Cannot set name handler after a session has already started.');
        }
        
        if(!preg_match('/^[a-zA-Z0-9]+$/', $pValue))
        {
            throw new \InvalidArgumentException('Session name contains invalid characters.');
        }
        
        session_name($pValue);
    }
    
    /**
     * @see SessionInterface::getName()
     */
    public function getName()
    {
        return session_name();
    }
    
    /**
     * @see SessionInterface::regenerate()
     */
    public function regenerate($pDeleteOldSession = true)
    {
         session_regenerate_id($pDeleteOldSession);
    }
    
    /**
     * @see SessionInterface::start()
     */
    public function start()
    {
        if(!$this->exist())
        {
            if(null !== $this->_saveHandler)
            {
                session_set_save_handler(
                    array($this->_saveHandler, 'open'),
                    array($this->_saveHandler, 'close'),
                    array($this->_saveHandler, 'read'),
                    array($this->_saveHandler, 'write'),
                    array($this->_saveHandler, 'destroy'),
                    array($this->_saveHandler, 'gc')
                );
                
                register_shutdown_function('session_write_close');
            }
            else
            {
                ini_set('session.save_handler', 'files');
            }
            
            session_start();
            $this->dispatch(new SessionEvent(SessionEvent::START));
        }
        
        $this->_parameters = new Parameters($_SESSION);
        $this->_parameters->setAutoSanitization(false);
    }
    
    /**
     * @see SessionInterface::has()
     */
    public function has($pKey)
    {
        return $this->_parameters->has($pKey);
    }
    
    /**
     * @see SessionInterface::get()
     */
    public function get($pKey, $pDefault = null)
    {
        return $this->_parameters->get($pKey, $pDefault);
    }
    
    /**
     * @see SessionInterface::set()
     */
    public function set($pKey, $pValue)
    {
        $this->_parameters->set($pKey, $pValue);
    }
    
    /**
     * @see SessionInterface::remove()
     */
    public function remove($pKey)
    {
        $this->_parameters->remove($pKey);
        
        if($this->_parameters->count() == 0)
        {
            $this->dispatch(new SessionEvent(SessionEvent::CLEAR));
        }
    }
    
    /**
     * @see SessionInterface::gets()
     */
    public function gets()
    {
        return $this->_parameters->gets();
    }
    
    /**
     * @see SessionInterface::sets()
     */
    public function sets(array $pData)
    {
        $this->_parameters->sets($pData);
        
        if(empty($pData))
        {
            $this->dispatch(new SessionEvent(SessionEvent::CLEAR));
        }
    }
    
    /**
     * @see Session::has()
     */
    public function offsetExists($pKey) 
    { 
        return $this->has($pKey);
    } 

    /**
     * @see Session::set()
     * @throws \InvalidArgumentException
     */
    public function offsetSet($pKey, $pValue) 
    { 
        if(null === $pKey)
        {
            throw new \InvalidArgumentException('Key parameter cannot be undefined.');
        }
        
        $this->set($pKey, $pValue);
    } 

    /**
     * @see Session::get()
     */
    public function offsetGet($pKey) 
    { 
        return $this->get($pKey);
    } 

    /**
     * @see Session::remove()
     */
    public function offsetUnset($pKey) 
    { 
        $this->remove($pKey);
    } 
    
    /**
     * @see Parameters::rewind()
     */
    public function rewind() 
    {
        $this->_parameters->rewind();
    }
    
    /**
     * @see Parameters::current()
     */
    public function current() 
    {
        return $this->_parameters->current();
    }
    
    /**
     * @see Parameters::key()
     */
    public function key() 
    {
        return $this->_parameters->key();
    }
    
    /**
     * @see Parameters::next()
     */
    public function next() 
    {
        $this->_parameters->next();
    }
    
    /**
     * @see Parameters::valid()
     */
    public function valid() 
    {
        return $this->_parameters->valid();
    }
    
    /**
     * @see Parameters::count()
     */
    public function count()
    {
        return $this->_parameters->count();
    }
    
    /**
     * @see Session::has()
     */
    public function __issset($pKey)
    {
        return $this->has($pKey);
    }
    
    /**
     * @see Session::get()
     */
    public function __get($pKey)
    {
        return $this->get($pKey);
    }
    
    /**
     * @see Session::set()
     */
    public function __set($pKey, $pValue)
    {
        $this->set($pKey, $pValue);
    }
    
    /**
     * @see Session::remove()
     */
    public function __unset($pKey)
    {
        $this->remove($pKey);
    }
    
    /**
     * @see SessionInterface::flash()
     */
    public function flash($pKey = null, $pValue = null)
    {
        $flash = $this->_parameters->get(self::FLASH_KEY, array());
        
        if(null === $pKey)
        {
            $result = $flash;
            $flash = array();
        }
        else
        {
            if(null === $pValue)
            {
                $result = null;

                if(isset($flash[$pKey]))
                {
                    $result = $flash[$pKey];
                    unset($flash[$pKey]);
                }
            }
            else
            {
                $flash[$pKey] = $pValue;
            }
        }

        $this->_parameters->set(self::FLASH_KEY, $flash);
        
        if(isset($result))
        {
            return $result;
        }
    }
    
    /**
     * @see SessionInterface::clear()
     */
    public function clear()
    {
        $this->_parameters->sets(array());
        $this->dispatch(new SessionEvent(SessionEvent::CLEAR));
    }
    
    /**
     * @see SessionInterface::destroy()
     */
    public function destroy()
    {
        if(!$this->exist())
        {
            return;
        }
        
        if(null !== $this->_parameters)
        {
            $this->clear();
        }
        
        if(ini_get('session.use_cookies')) 
        {
            $params = session_get_cookie_params();

            setcookie(
                $this->getName(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        $this->dispatch(new SessionEvent(SessionEvent::DESTROY));
    }
}