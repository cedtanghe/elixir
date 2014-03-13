<?php

namespace Elixir\Module\Application\Listener;

use Elixir\DI\ContainerInterface;
use Elixir\Dispatcher\Dispatcher;
use Elixir\Dispatcher\SubscriberInterface;
use Elixir\MVC\ApplicationEvent;
use Elixir\MVC\ApplicationInterface;
use Elixir\MVC\Exception\NotFoundException;
use Elixir\MVC\Module\SelectedInterface;
use Elixir\Routing\Route;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Listeners implements SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected $_container;
    
    /**
     * @param ContainerInterface $pContainer
     */
    public function __construct(ContainerInterface $pContainer)
    {
        $this->_container = $pContainer;
    }

    /**
     * @see SubscriberInterface::subscribe()
     */
    public function subscribe(Dispatcher $pDispatcher)
    {   
        /************ FILTER REQUEST ************/
        
        $pDispatcher->addListener(ApplicationEvent::FILTER_REQUEST, array($this, 'onSelectedModules'), -1);
        $pDispatcher->addListener(ApplicationEvent::FILTER_REQUEST, array($this, 'onMatchRoute'), 900);
        $pDispatcher->addListener(ApplicationEvent::FILTER_REQUEST, array($this, 'onConfigureURL'), 1000);
        
        /************ EXCEPTIONS ************/
        
        $pDispatcher->addListener(ApplicationEvent::EXCEPTION_403, array($this, 'onExceptionCatched'));
        $pDispatcher->addListener(ApplicationEvent::EXCEPTION_404, array($this, 'onExceptionCatched'));
        $pDispatcher->addListener(ApplicationEvent::EXCEPTION_500, array($this, 'onExceptionCatched'));
    }
    
    /**
     * @see SubscriberInterface::unsubscribe()
     */
    public function unsubscribe(Dispatcher $pDispatcher)
    {
        // Not yet
    }
    
    /**
     * @internal
     * @param ApplicationEvent $e
     */
    public function onConfigureURL(ApplicationEvent $e)
    {
        if($e->getRequestType() === ApplicationInterface::MAIN_REQUEST)
        {
            $config = $this->_container->get('config');
            
            if(isset($config['url']))
            {
                $e->getRequest()->setBaseURL($config['url']);
            }
        }
    }
    
    /**
     * @internal
     * @param ApplicationEvent $e
     * @throws NotFoundException
     */
    public function onMatchRoute(ApplicationEvent $e)
    {
        if($e->getRequestType() === ApplicationInterface::MAIN_REQUEST)
        {
            $router = $this->_container->get('router');
            $router->setRequest($e->getRequest());

            $match = $router->match();
            
            if(null !== $match)
            {
                $e->getRequest()->setRoute($match);

                foreach($match->gets() as $key => $value)
                {
                    switch($key)
                    {
                        case Route::MODULE:
                        case Route::CONTROLLER:
                        case Route::ACTION:
                            $e->getRequest()->{'set' . ltrim(ucfirst($key), '_')}($value);
                        default:
                            $e->getRequest()->getAttributes()->set($key, $value);
                        break;
                    }
                }
            }
            else
            {
                throw new NotFoundException('The router found no correspondence');
            }
        }
    }
    
    /**
     * @internal
     * @param ApplicationEvent $e
     */
    public function onSelectedModules(ApplicationEvent $e)
    {
        $application = $this->_container->get('application');
        $hierarchy = $application->getModuleHierarchy($e->getRequest()->getModule(), array());
        
        foreach($hierarchy as $m)
        {
            $module = $application->getModule($m);
            
            if($module instanceof SelectedInterface)
            {
                $module->selected();
            }
        }
    }
    
    /**
     * @internal
     * @param ApplicationEvent $e
     */
    public function onExceptionCatched(ApplicationEvent $e)
    {
        if($e->getRequestType() === ApplicationInterface::MAIN_REQUEST)
        {
            $application = $this->_container->get('application');
            $module = $e->getRequest()->getModule();
            
            $e->getRequest()->getAttributes()->sets(array('exception' => $e->getException()));

            if(!$application->hasModule(preg_match('/\(@([^\)]+)\)/', $module, $matches) ? $matches[1] : $module))
            {
                $module = '(@Application)';
            }
           
            $e->getRequest()->setModule($module);
            $e->getRequest()->setController('error');
            $e->getRequest()->setAction('index');
        }
    }
}