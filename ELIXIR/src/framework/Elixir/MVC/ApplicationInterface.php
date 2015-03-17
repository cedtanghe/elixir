<?php

namespace Elixir\MVC;

use Elixir\DI\ContainerInterface;
use Elixir\Dispatcher\DispatcherInterface;
use Elixir\HTTP\Request;
use Elixir\HTTP\Response;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface ApplicationInterface extends DispatcherInterface
{
    /**
     * @var string
     */
    const MAIN_REQUEST = 'main_request';
    
    /**
     * @var string
     */
    const SUB_REQUEST = 'sub_request';
    
    /**
     * @return ContainerInterface
     */
    public function getContainer();
    
    /**
     * @param string $pName
     * @param mixed $pDefault
     * @return mixed
     */
    public function getModule($pName, $pDefault = null);
    
    /**
     * @return array
     */
    public function getModules();
    
    /**
     * @param string $pClassName
     * @return string|null
     */
    public function locateClass($pClassName);
    
    /**
     * @param string $pFilePath
     * @param boolean $pAll
     * @return string|array|null
     */
    public function locateFile($pFilePath, $pAll = false);
    
    /**
     * @return boolean
     */
    public function isBooted();
    
    public function boot();
    
    /**
     * @param Request $pRequest
     * @param string $pType
     * @return Response
     */
    public function handle(Request $pRequest, $pType = self::MAIN_REQUEST);
}
