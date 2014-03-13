<?php

namespace Elixir\Helper;

use Elixir\Util\Str;
use Elixir\HTTP\Response;
use Elixir\HTTP\ResponseFactory;
use Elixir\MVC\ApplicationInterface;
use Elixir\MVC\Controller\ControllerInterface;
use Elixir\MVC\Controller\Helper\ContextInterface;
use Elixir\View\Storage\StorageInterface;
use Elixir\View\ViewInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Renderer implements ContextInterface, HelperInterface
{
    /**
     * @var ControllerInterface
     */
    protected $_context;
    
    /**
     * @var ApplicationInterface 
     */
    protected $_application;
    
    /**
     * @param ApplicationInterface $pApplication
     */
    public function __construct(ApplicationInterface $pApplication = null)
    {
        $this->_application = $pApplication;
    }
    
    /**
     * @see ContextInterface::setController();
     */
    public function setController(ControllerInterface $pValue)
    {
        $this->_context = $pValue;
    }
    
    /**
     * @param string|StorageInterface $pTemplate
     * @param array|ViewInterface $pDataOrView
     * @return string
     */
    public function render($pTemplate, $pDataOrView = array())
    {
        if(is_array($pDataOrView))
        {
            $view = $this->_context->getContainer()->get('view');
            
            foreach($pDataOrView as $key => $value)
            {
                $view->set($key, $value);
            }
        }
        else
        {
            $view = $pDataOrView;
        }
        
        if(null === $pTemplate)
        {
            $module = Str::slug($this->_context->getRequest()->getModule());
            
            if(!preg_match('/\(@([^\)]+)\)/', $module))
            {
                $module = '(@' . $module . ')';
            }
            
            $pTemplate = sprintf(
                '%s/resources/views/%s/%s.%s', 
                $module,
                Str::slug($this->_context->getRequest()->getController()),
                Str::slug($this->_context->getRequest()->getAction()),
                method_exists($view, 'getDefaultExtension') ? $view->getDefaultExtension() : 'phtml'
            );
        }
        
        if(is_string($pTemplate))
        {
            if(null === $this->_application)
            {
                $this->_application = $this->_context->getContainer()->get('application');
            }
        
            $pTemplate = $this->_application->locateFile($pTemplate, false);
        }
        
        return $view->render($pTemplate);
    }
    
    /**
     * @param string|StorageInterface $pTemplate
     * @param array|ViewInterface $pDataOrView
     * @param integer $pStatus
     * @param string $pProtocol
     * @param array $pHeaders
     * @return Response
     */
    public function renderResponse($pTemplate,
                                   $pDataOrView = array(),
                                   $pStatus = 200,
                                   $pProtocol = null,
                                   array $pHeaders = array())
    {
        return ResponseFactory::create(
            $this->render($pTemplate, $pDataOrView),
            $pStatus,
            $pProtocol,
            $pHeaders
        );
    }
    
    /**
     * @param string $pText
     * @param integer $pStatus
     * @param string $pProtocol
     * @param array $pHeaders
     * @return Response
     */
    public function renderTextResponse($pText,
                                       $pStatus = 200,
                                       $pProtocol = null,
                                       array $pHeaders = array())
    {
        return ResponseFactory::create(
            $pText,
            $pStatus,
            $pProtocol,
            $pHeaders
        );
    }

    /**
     * @see Renderer::render()
     */
    public function direct()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'render'), $args);
    }
}