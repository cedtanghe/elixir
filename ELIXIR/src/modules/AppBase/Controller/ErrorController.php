<?php

namespace Elixir\Module\AppBase\Controller;

use Elixir\HTTP\Response;
use Elixir\HTTP\ResponseFactory;
use Elixir\HTTP\Sanitizer;
use Elixir\MVC\Controller\ControllerAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class ErrorController extends ControllerAbstract
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        $message = 'An error has occurred !';
        $statusCode = 500;
        
        $exception = $this->_request->getAttributes('exception');
        
        if(null !== $exception)
        {
            switch($exception->getCode())
            {
                case 403:
                    $message = 'Access Denied !';
                    $statusCode = 403;
                break;
                case 404:
                    $message = 'Page not found !';
                    $statusCode = 404;
                break;
            }
        }
        
        $start = $exception->getLine() - 4;
        $lines = array_slice(file($exception->getFile()), $start < 0 ? 0 : $start, 7);

        $exception = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'status-code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => implode('', $lines)
        ];
	
        return $this->render(['message' => $message, 'exception' => $exception], $statusCode);
    }
    
    /**
     * @param array $pData
     * @param integer $pStatusCode
     * @return Response
     */
    protected function render(array $pData, $pStatusCode)
    {
        $sanitizer = new Sanitizer();
        $pData = $sanitizer->filter($pData);
        
        if(defined('APPLICATION_ENV') && APPLICATION_ENV != 'production')
        {
            $data = '<pre>' . print_r($pData, true) . '</pre>';
        }
        else
        {
            $data = $pData['message'];
        }
        
        if($this->_request->isAjax())
        {
            return ResponseFactory::json(json_encode($data), $pStatusCode);
        }
        
        return $this->helper('helper.render')->renderTextResponse(
            $data,
            $pStatusCode
        );
    }
}
