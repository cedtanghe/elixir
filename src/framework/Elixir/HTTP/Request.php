<?php

namespace Elixir\HTTP;

use Elixir\Routing\Matcher\RouteMatch;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Request
{
    /**
     * @var string
     */
    const ATTRIBUTES = 'attributes';
    
    /**
     * @var string
     */
    const QUERY = 'query';
    
    /**
     * @var string
     */
    const POST = 'post';
    
    /**
     * @var string
     */
    const SESSION = 'session';
    
    /**
     * @var string
     */
    const COOKIE = 'cookie';
    
    /**
     * @var string
     */
    const FILES = 'files';
    
    /**
     * @var string
     */
    const SERVER = 'server';
    
    /**
     * @var string
     */
    const ENV = 'env';
    
    /**
     * @var Headers
     */
    protected $_headers;
    
    /**
     * @var ParametersInterface 
     */
    protected $_attributes;
    
    /**
     * @var ParametersInterface 
     */
    protected $_query;
    
    /**
     * @var ParametersInterface 
     */
    protected $_post;
    
    /**
     * @var ParametersInterface 
     */
    protected $_session;
    
    /**
     * @var ParametersInterface 
     */
    protected $_cookie;
    
    /**
     * @var ParametersInterface 
     */
    protected $_files;
    
    /**
     * @var ParametersInterface 
     */
    protected $_server;
    
    /**
     * @var ParametersInterface 
     */
    protected $_env;
    
    /**
     * @var string
     */
    protected $_base;
    
    /**
     * @var string
     */
    protected $_URL;

    /**
     * @param ParametersInterface|\ArrayAccess|array $pAttributes
     * @param ParametersInterface|\ArrayAccess|array $pQuery
     * @param ParametersInterface|\ArrayAccess|array $pPost
     * @param ParametersInterface|\ArrayAccess|array $pSession
     * @param ParametersInterface|\ArrayAccess|array $pCookie
     * @param ParametersInterface|\ArrayAccess|array $pFiles
     * @param ParametersInterface|\ArrayAccess|array $pServer
     * @param ParametersInterface|\ArrayAccess|array $pEnv
     * @throws \InvalidArgumentException
     */
    public function __construct($pAttributes,
                                $pQuery,
                                $pPost,
                                $pSession,
                                $pCookie,
                                $pFiles,
                                $pServer,
                                $pEnv) 
    {
        $providers = array(
            self::ATTRIBUTES => $pAttributes,
            self::QUERY => $pQuery,
            self::POST => $pPost,
            self::SESSION => $pSession,
            self::COOKIE => $pCookie,
            self::FILES => $pFiles,
            self::SERVER => $pServer,
            self::ENV => $pEnv
        );
        
        foreach($providers as $key => $value)
        {
            if(!$value instanceof ParametersInterface)
            {
                if(!($value instanceof \ArrayAccess || is_array($value)))
                {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Key "%s" must be of type array' .
                            ' or interface implementer "\Elixir\HTTP\ParametersInterface"' .
                            ' or "ArrayAccess".',
                            $key
                        )
                    );
                }
                
                $value = new Parameters($value);
            }
            
            $this->{'_' . $key} = $value;
        }
    }
    
    /**
     * @param Headers $pValue
     */
    public function setHeaders(Headers $pValue)
    {
        $this->_headers = $pValue;
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @return Headers|mixed
     */
    public function getHeaders($pKey = null, $pDefault = null)
    {
        if(null === $this->_headers || null === $pKey)
        {
            return $this->_headers;
        }
        
        return $this->_headers->get($pKey, $pDefault);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @param mixed $pSanitize
     * @param array $pProviders
     * @return mixed
     */
    public function get($pKey, $pDefault = null, $pSanitize = null, $pProviders = array(self::QUERY,
                                                                                        self::POST,
                                                                                        self::ATTRIBUTES))
    {
        foreach($pProviders as $provider)
        {
            $parameters = $this->{'_' . $provider};
            
            if($parameters->has($pKey))
            {
                return $parameters->get($pKey, $pDefault, $pSanitize);
            }
        }
        
        return $pDefault;
    }
    
    /**
     * @param string $pKey
     * @param array $pProviders
     * @return boolean
     */
    public function has($pKey, $pProviders = array(self::QUERY,
                                                   self::POST,
                                                   self::ATTRIBUTES))
    {
        foreach($pProviders as $provider)
        {
            $parameters = $this->{'_' . $provider};
            
            if($parameters->has($pKey))
            {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @param mixed $pSanitize
     * @return ParametersInterface|mixed
     */
    public function getAttributes($pKey = null, $pDefault = null, $pSanitize = null)
    {
        if(null === $pKey)
        {
            return $this->_attributes;
        }
        
        return $this->_attributes->get($pKey, $pDefault, $pSanitize);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @param mixed $pSanitize
     * @return ParametersInterface|mixed
     */
    public function getQuery($pKey = null, $pDefault = null, $pSanitize = null)
    {
        if(null === $pKey)
        {
            return $this->_query;
        }
        
        return $this->_query->get($pKey, $pDefault, $pSanitize);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @param mixed $pSanitize
     * @return ParametersInterface|mixed
     */
    public function getPost($pKey = null, $pDefault = null, $pSanitize = null)
    {
        if(null === $pKey)
        {
            return $this->_post;
        }
        
        return $this->_post->get($pKey, $pDefault, $pSanitize);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @param mixed $pSanitize
     * @return ParametersInterface|mixed
     */
    public function getSession($pKey = null, $pDefault = null, $pSanitize = null)
    {
        if(null === $pKey)
        {
            return $this->_session;
        }
        
        return $this->_session->get($pKey, $pDefault, $pSanitize);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @param mixed $pSanitize
     * @return ParametersInterface|mixed
     */
    public function getCookie($pKey = null, $pDefault = null, $pSanitize = null)
    {
        if(null === $pKey)
        {
            return $this->_cookie;
        }
        
        return $this->_cookie->get($pKey, $pDefault, $pSanitize);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @param mixed $pSanitize
     * @return ParametersInterface|mixed
     */
    public function getFiles($pKey = null, $pDefault = null, $pSanitize = null)
    {
        if(null === $pKey)
        {
            return $this->_files;
        }
        
        return $this->_files->get($pKey, $pDefault, $pSanitize);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @param mixed $pSanitize
     * @return ParametersInterface|mixed
     */
    public function getServer($pKey = null, $pDefault = null, $pSanitize = null)
    {
        if(null === $pKey)
        {
            return $this->_server;
        }
        
        return $this->_server->get($pKey, $pDefault, $pSanitize);
    }
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @param mixed $pSanitize
     * @return ParametersInterface|mixed
     */
    public function getEnv($pKey = null, $pDefault = null, $pSanitize = null)
    {
        if(null === $pKey)
        {
            return $this->_env;
        }
        
        return $this->_env->get($pKey, $pDefault, $pSanitize);
    }

    /**
     * @param string $pValue 
     */
    public function setBaseURL($pValue)
    {
        $this->_base = rtrim($pValue, '/');
    }

    /**
     * @return string 
     */
    public function getBaseURL()
    {
        if(null === $this->_base)
        {
            $this->setBaseURL($this->getScheme() . $this->getServer('HTTP_HOST', ''));
        }
        
        return $this->_base;
    }
    
    /**
     * @param string $pValue 
     */
    public function setURL($pValue)
    {
        $this->_URL = rtrim($pValue, '/');
    }
    
    /**
     * @return string
     */
    public function getURL()
    {
        if(null === $this->_URL)
        {
            $this->setURL(
                $this->getScheme() . 
                $this->getServer('HTTP_HOST', '') . 
                $this->getServer('REQUEST_URI', '')
            );
        }
        
        return $this->_URL;
    }
    
    /**
     * @return string
     */
    public function getIP()
    {
        return $this->getServer(
            'HTTP_X_FORWARDED_FOR', 
            $this->getServer('HTTP_CLIENT_IP', $this->getServer('REMOTE_ADDR'))
        );
    }
    
    /**
     * @return boolean 
     */
    public function isSecure()
    {
        $HTTPS = $this->getServer('HTTPS');
        return (strtoupper($HTTPS) == 'ON' || $HTTPS == 1) ? true : false;
    }
    
    /**
     * @param boolean $pSecure
     * @return string 
     */
    public function getScheme($pSecure = null)
    {
        if(null === $pSecure)
        {
            $pSecure = $this->isSecure();
        }
        
        return $pSecure ? 'https://' : 'http://';
    }
    
    /**
     * @return string 
     */
    public function getPathInfo()
    {
        $pathInfo = str_replace($this->getBaseURL(), '', $this->getURL());
        $qpos = strpos($pathInfo, '?');
                
        if (false !== $qpos) 
        {
            $pathInfo = substr($pathInfo, 0, $qpos);
        }
        
        return '/' . ltrim($pathInfo, '/');
    }
    
    /**
     * @param mixed $pDefault
     * @return string|mixed 
     */
    public function getRequestMethod($pDefault = null)
    {
        return strtoupper($this->getServer('REQUEST_METHOD', $pDefault));
    }

    /**
     * @return boolean 
     */
    public function isPost()
    {
        return 'POST' === $this->getRequestMethod('GET');
    }
    
    /**
     * @return boolean 
     */
    public function isQuery()
    {
        $method = $this->getRequestMethod('GET');
        return 'GET' === $method || 'HEAD' === $method;
    }
    
    /**
     * @return boolean 
     */
    public function isPut()
    {
        return 'PUT' === $this->getRequestMethod('GET');
    }
    
    /**
     * @return boolean 
     */
    public function isDelete()
    {
        return 'DELETE' === $this->getRequestMethod('GET');
    }
    
    /**
     * @return boolean 
     */
    public function isAjax()
    {
        return 'XMLHTTPREQUEST' === strtoupper($this->getServer('HTTP_X_REQUESTED_WITH', ''));
    }
    
    /**
     * @return string|null 
     */
    public function getUser()
    {
        return $this->getServer('PHP_AUTH_USER');
    }
    
    /**
     * @return string|null 
     */
    public function getPassword()
    {
        return $this->getServer('PHP_AUTH_PW');
    }
    
    /**
     * @return RouteMatch|null 
     */
    public function getRoute()
    {
        return $this->getAttributes('_route');
    }
    
    /**
     * @param RouteMatch $pValue
     */
    public function setRoute(RouteMatch $pValue)
    {
        $this->getAttributes()->set('_route', $pValue);
    }
    
    /**
     * @return string|null 
     */
    public function getModule()
    {
        $route = $this->getRoute();
        
        if(null !== $route)
        {
            $module = $route->get('_module');
            
            if(null !== $module)
            {
                return $module;
            }
        }
        
        return $this->getAttributes('_module');
    }
    
    /**
     * @param string $pValue
     */
    public function setModule($pValue)
    {
        $route = $this->getRoute();
        
        if(null !== $route)
        {
            $route->set('_module', $pValue);
        }
        else
        {
            $this->getAttributes()->set('_module', $pValue);
        }
    }

    /**
     * @return string|null 
     */
    public function getController()
    {
        $route = $this->getRoute();
        
        if(null !== $route)
        {
            $controller = $route->get('_controller');
            
            if(null !== $controller)
            {
                return $controller;
            }
        }
        
        return $this->getAttributes('_controller');
    }
    
    /**
     * @param string $pValue
     */
    public function setController($pValue)
    {
        $route = $this->getRoute();
        
        if(null !== $route)
        {
            $route->set('_controller', $pValue);
        }
        else
        {
            $this->getAttributes()->set('_controller', $pValue);
        }
    }
    
    /**
     * @return string|null 
     */
    public function getAction()
    {
        $route = $this->getRoute();
        
        if(null !== $route)
        {
            $action = $route->get('_action');
            
            if(null !== $action)
            {
                return $action;
            }
        }
        
        return $this->getAttributes('_action');
    }
    
    /**
     * @param string $pValue
     */
    public function setAction($pValue)
    {
        $route = $this->getRoute();
        
        if(null !== $route)
        {
            $route->set('_action', $pValue);
        }
        else
        {
            $this->getAttributes()->set('_action', $pValue);
        }
    }
}