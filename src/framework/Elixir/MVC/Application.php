<?php

namespace Elixir\MVC;

use Elixir\Cache\CacheInterface;
use Elixir\DI\ContainerInterface;
use Elixir\Dispatcher\Dispatcher;
use Elixir\HTTP\Request;
use Elixir\HTTP\Response;
use Elixir\HTTP\ResponseFactory;
use Elixir\HTTP\Session\SessionInterface;
use Elixir\MVC\Controller\ControllerResolverInterface;
use Elixir\MVC\Module\ModuleInterface;
use Elixir\Util\Arr;
use Elixir\Util\Str;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Application extends Dispatcher implements ApplicationInterface
{
    /**
     * The container is accessible from anywhere for convenience
     * 
     * @var ContainerInterface|null
     */
    public static $registry;

    /**
     * @var string
     */
    const DEFAULT_CACHE_KEY = '___CACHE_APPLICATION___';
    
    /**
     * @var ContainerInterface
     */
    protected $_container;
    
    /**
     * @var array
     */
    protected $_modules = array();

    /**
     * @var array 
     */
    protected $_map = array();
    
    /**
     * @var array 
     */
    protected $_classesLoaded = array();
    
    /**
     * @var array 
     */
    protected $_filesLoaded = array();
    
    /**
     * @var ControllerResolverInterface
     */
    protected $_controllerResolver;
    
    /**
     * @var boolean
     */
    protected $_booted = false;
    
    /**
     * @param ContainerInterface $pContainer
     */
    public function __construct(ContainerInterface $pContainer)
    {
        $this->_container = $pContainer;
        $this->_container->set('application', $this);
        
        static::$registry = $this->_container;
    }
    
    /**
     * @see ApplicationInterface::getContainer()
     */
    public function getContainer()
    {
        return $this->_container;
    }
    
    /**
     * @param CacheInterface|SessionInterface $pCache
     * @param string $pKey
     */
    public function loadFromCache($pCache, $pKey = self::DEFAULT_CACHE_KEY)
    {
        $data = $pCache->get($pKey, array()) ?: array();
        
        $this->_classesLoaded = array_merge(
            Arr::get('classes', $data, array()),
            $this->_classesLoaded
        );
        
        $this->_filesLoaded = array_merge(
            Arr::get('files', $data, array()),
            $this->_filesLoaded
        );
    }
    
    /**
     * @param CacheInterface|SessionInterface $pCache
     * @param string $pKey
     */
    public function exportToCache($pCache, $pKey = self::DEFAULT_CACHE_KEY)
    {
        $pCache->set(
            $pKey, 
            array(
                'classes' => $this->_classesLoaded,
                'files' => $this->_filesLoaded
            )
        );
    }
    
    /**
     * @param ControllerResolverInterface $pValue
     */
    public function setControllerResolver(ControllerResolverInterface $pValue)
    {
        $this->_controllerResolver = $pValue;
    }
    
    /**
     * @return ControllerResolverInterface
     */
    public function getControllerResolver()
    {
        return $this->_controllerResolver;
    }
    
    /**
     * @param string $pName
     * @return boolean
     */
    public function hasModule($pName)
    {
        foreach($this->_modules as $module)
        {
            if($module->getName() == $pName)
            {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @see ApplicationInterface::getModule()
     */
    public function getModule($pName, $pDefault = null)
    {
        foreach($this->_modules as $module)
        {
            if($module->getName() == $pName)
            {
                return $module;
            }
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }
    
    /**
     * @param ModuleInterface $pModule
     * @throws \LogicException
     */
    public function addModule(ModuleInterface $pModule)
    {
        if($this->_booted)
        {
            throw new \LogicException('You can not add more modules after booted the application.');
        }
        
        $name = $pModule->getName();
        
        if($this->hasModule($name))
        {
            throw new \LogicException(sprintf('A module with name "%s" is already registered.', $name));
        }
        
        $required = $pModule->getRequired();
        
        if(null !== $required)
        {
            foreach((array)$required as $module)
            {
                if(!$this->hasModule($module))
                {
                    throw new \LogicException(sprintf('The "%s" module requires the use of the module "%s".', $name, $module));
                }
            }
        }
        
        $parent = $pModule->getParent();
        
        if(null !== $parent)
        {
            if(!$this->hasModule($parent))
            {
                throw new \LogicException(sprintf('Module "%s" extends the unregistered module "%s".', $name, $parent));
            }
        }
        
        $this->_modules[] = $pModule;
        $pModule->register($this, $this->_container);
    }
    
    /**
     * @see ApplicationInterface::getModules()
     */
    public function getModules()
    {
        return $this->_modules;
    }
    
    /**
     * @param array $pData
     */
    public function setModules(array $pData)
    {
        $this->_modules = array();
        
        foreach($pData as $module)
        {
            $this->addModule($module);
        }
    }
    
    /**
     * @see ApplicationInterface::isBooted()
     */
    public function isBooted()
    {
        return $this->_booted;
    }

    /**
     * @throws \RuntimeException
     * @see ApplicationInterface::boot()
     */
    public function boot()
    {
        if($this->_booted)
        {
            return;
        }
        
        $extended = array();
        
        foreach($this->_modules as $module)
        {
            $name = $module->getName();
            $parent = $module->getParent();
            
            if($parent == $name)
            {
                $parent = null;
            }
            
            if(null !== $parent)
            {
                if(!empty($extended[$parent]))
                {
                    throw new \LogicException(
                        sprintf(
                            'Module "%s" trying to extend a module already registered(%s).',
                            $name, 
                            $parent
                        )
                    );
                }
                
                $extended[$parent] = $name;
            }
            
            $extended[$name] = null;
        }
        
        $this->_map = array();
        
        foreach($extended as $key => $value)
        {
            $this->_map[$key] = array($key);
            
            if(null !== $value)
            {
                $this->_map[$key][] = $value;
                $child = $extended[$value];
                
                while(null !== $child)
                {
                    $this->_map[$key][] = $child;
                    $child = $extended[$child];
                }
            }
            
            $this->_map[$key] = array_reverse($this->_map[$key]);
        }
        
        foreach($this->_modules as $module)
        {
            $module->boot();
        }
        
        $this->dispatch(new ApplicationEvent(ApplicationEvent::MODULES_BOOTED));
        $this->_booted = true;
    }
    
    /**
     * @param string $pModule
     * @param mixed $pDefault
     * @return mixed
     * @throws \LogicException
     */
    public function getModuleHierarchy($pModule, $pDefault = null)
    {
        if(!$this->_booted)
        {
            throw new \LogicException('The application must first be booted.');
        }
        
        if(preg_match('/^\(@([^\)]+)\)$/', $pModule, $matches))
        {
            $pModule = $matches[1];
        }
        
        $module = ucfirst(Str::camelize($pModule));
        
        foreach($this->_map as $key => $value)
        {
            if(in_array($module, $value))
            {
                return $this->_map[$key];
            }
        }
        
        return is_callable($pDefault) ? $pDefault() : $pDefault;
    }

    /**
     * @see ApplicationInterface::locateClass()
     */
    public function locateClass($pClassName)
    {
        if(isset($this->_classesLoaded[$pClassName]))
        {
            return $this->_classesLoaded[$pClassName];
        }
        
        $search = array();
        
        if(preg_match('/^\(@([^\)]+)\)/', $pClassName, $matches))
        {
            if($this->hasModule($matches[1]))
            {
                foreach($this->_map[$matches[1]] as $module)
                {
                    $search[] = str_replace($matches[0], $this->getModule($module)->getNamespace(), $pClassName);
                }
            }
        }
        else
        {
            $search = array($pClassName);
        }
        
        foreach($search as $class)
        {
            if(class_exists($class))
            {
                $this->_classesLoaded[$pClassName] = $class;
                return $class;
            }
        }
        
        return null;
    }
    
    /**
     * @see ApplicationInterface::locateFile()
     */
    public function locateFile($pFilePath, $pAll = false)
    {
        if(isset($this->_filesLoaded[$pFilePath]))
        {
            $files = $this->_filesLoaded[$pFilePath];
            return $pAll ? $files : $files[0];
        }
        
        $search = array();

        if(preg_match('/\(@([^\)]+)\)/', $pFilePath, $matches))
        {
            if($this->hasModule($matches[1]))
            { 
                $path = false;
                
                if(strpos($pFilePath, $matches[0]) === 0)
                {
                    $path = true;
                }
                
                foreach($this->_map[$matches[1]] as $module)
                {
                    $search[] = str_replace(
                        $matches[0], 
                        $path ? $this->getModule($module)->getPath() : $this->getModule($module)->getName(),
                        $pFilePath
                    );
                }
            }
        }
        else
        {
            $search = array($pFilePath);
        }

        $files = array();

        foreach($search as $file)
        {
            if(file_exists($file))
            {
                $files[] = $file;
            }
        }
        
        if(count($files) > 0)
        {
            $this->_filesLoaded[$pFilePath] = $files;
            return $pAll ? $files : $files[0];
        }
        
        return $pFilePath;
    }
    
    /**
     * @see ApplicationInterface::handle()
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function handle(Request $pRequest, $pType = self::MAIN_REQUEST)
    {
        if(null === $this->_controllerResolver)
        {
            throw new \RuntimeException('The ControllerResolver class is not defined.');
        }
        
        $request = $pRequest;
        $type = $pType;
        
        if($type == self::MAIN_REQUEST)
        {
            $this->_container->set('request', $request);
        }
        
        $this->boot();
        
        if($type == self::MAIN_REQUEST)
        {
            $this->dispatch(new ApplicationEvent(ApplicationEvent::START));
        }
        
        $loop = true;
        
        while($loop)
        {
            try
            {
                $loop = false;
                
                $event = new ApplicationEvent(ApplicationEvent::FILTER_REQUEST, $request, $type);
                $this->dispatch($event);
                
                $response = $event->getResponse();
                
                if(null === $response)
                {
                    $controller = $this->_controllerResolver->getController($this, $request);
                    $arguments = $this->_controllerResolver->getArguments($request, $controller);

                    $this->dispatch(new ApplicationEvent(ApplicationEvent::PRE_CONTROLLER, $request, $type));
                    $response = call_user_func_array($controller, $arguments);

                    if(!($response instanceof Response))
                    {
                        $response = ResponseFactory::create($response, empty($response) ? 204 : 200, $request->getServer('SERVER_PROTOCOL', 'HTTP/1.1'));
                    }

                    $this->dispatch(new ApplicationEvent(ApplicationEvent::POST_CONTROLLER, $request, $type, $response));
                }
                
                $this->dispatch(new ApplicationEvent(ApplicationEvent::FILTER_RESPONSE, $request, $type, $response));
            }
            catch(\Exception $e)
            {
                switch($e->getCode())
                {
                    case 403:
                        $eventType = ApplicationEvent::EXCEPTION_403;
                        $statusCode = 403;
                    break;
                    case 404:
                        $eventType = ApplicationEvent::EXCEPTION_404;
                        $statusCode = 404;
                    break;
                    default:
                        $eventType = ApplicationEvent::EXCEPTION_500;
                        $statusCode = 500;
                    break;
                }
                
                $attributes = $request->getAttributes()->gets();
                $event = new ApplicationEvent($eventType, $request, $type, null, $e);
                $this->dispatch($event);
                
                $response = $event->getResponse();
                
                if(null === $response)
                {
                    foreach($request->getAttributes()->gets() as $key => $value)
                    {
                        if(!isset($attributes[$key]) || $value !== $attributes[$key])
                        {
                            $type = self::SUB_REQUEST;
                            $loop = true;
                            
                            continue 2;
                        }
                    }
                    
                    throw $e;
                }
                else
                {
                    $response->setStatusCode($statusCode);
                }
                
                $this->dispatch(new ApplicationEvent(ApplicationEvent::FILTER_RESPONSE, $request, $type, $response));
            }
        }
        
        if($type == self::MAIN_REQUEST)
        {
            $this->dispatch(new ApplicationEvent(ApplicationEvent::COMPLETE));
        }
        
        return $response;
    }
    
    /**
     * @param Request $pRequest
     * @param Response $pResponse
     */
    public function terminate(Request $pRequest, Response $pResponse)
    {
        if(!$pResponse->isSent())
        {
            if(null === $pResponse->getRequest())
            {
                $pResponse->setRequest($pRequest);
            }
            
            $pResponse->send();
        }
        
        $this->dispatch(new ApplicationEvent(ApplicationEvent::TERMINATE, $pRequest, null, $pResponse));
    }
}