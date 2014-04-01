<?php

namespace Elixir\Module\Application\Controller;

use Elixir\HTTP\Response;
use Elixir\MVC\Controller\ControllerAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

final class Dummy
{
    public function translate($pStr)
     {
         return $pStr;
     }
}

class ErrorController extends ControllerAbstract
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        $translator = $this->_container->get('helper.i18n', null, function()
        {
            return new Dummy();
        });
        
        $message = $translator->translate('An error has occurred !');
        $statusCode = 500;
        
        $exception = $this->_request->getAttributes('exception');
        
        if(null !== $exception)
        {
            switch($exception->getCode())
            {
                case 403:
                    $message = $translator->translate('Access Denied !');
                    $statusCode = 403;
                break;
                case 404:
                    $message = $translator->translate('Page not found !');
                    $statusCode = 404;
                break;
            }
        }
        
        $start = $exception->getLine() - 4;
        $lines = array_slice(file($exception->getFile()), $start < 0 ? 0 : $start, 7);

        $exception = array(
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'status-code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => implode('', $lines)
        );
		
        return $this->render(array('message' => $message, 'exception' => $exception), $statusCode);
    }
    
    /**
     * @param array $pData
     * @param integer $pStatusCode
     * @return Response
     */
    protected function render(array $pData, $pStatusCode)
    {
        return $this->helper('helper.render')->renderTextResponse(
            $pData['message'],
            $pStatusCode
        );
    }
}
