<?php

namespace Elixir\HTTP;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class ResponseFactory
{
    /**
     * @param string $pContent
     * @param integer $pStatus
     * @param string $pProtocol
     * @param array $pHeaders
     * @return Response
     */
    public static function create($pContent = '',
                                  $pStatus = 200,
                                  $pProtocol = null,
                                  array $pHeaders = array())
    {
        return new Response(
            $pContent,
            $pStatus,
            null !== $pProtocol ? $pProtocol : (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1'),
            $pHeaders
        );
    }
    
    /**
     * @param string $pLocation
     * @param integer $pStatus
     * @param type $pProtocol
     * @param array $pHeaders
     */
    public static function redirect($pLocation,
                                    $pStatus = 302, 
                                    $pProtocol = null,
                                    array $pHeaders = array())
    {
        $response = static::create(
            '',
            $pStatus,
            $pProtocol,
            array_merge($pHeaders, array('Location' => $pLocation))
        );
        
        $response->send();
        exit();
    }
    
    /**
     * @param string $pContent
     * @param integer $pStatus
     * @param string $pProtocol
     * @param array $pHeaders
     * @return Response
     */
    public static function json($pContent = '',
                                $pStatus = 200,
                                $pProtocol = null,
                                array $pHeaders = array())
    {
        return static::create(
            $pContent, 
            $pStatus, 
            $pProtocol, 
            array_merge($pHeaders, array('Content-Type' => 'application/json'))
        );
    }

    /**
     * @param string $pContent
     * @param string $pFileName
     * @param string $pStatus
     * @param string $pProtocol
     * @param array $pHeaders
     * @return Response|void
     */
    public static function stream($pContent,
                                  $pFileName = null,
                                  $pStatus = 200,
                                  $pProtocol = null,
                                  array $pHeaders = array('Content-Type' => 'application/octet-stream'))
    {
        $file = null !== $pFileName;
        
        if($file)
        {
            $pHeaders['Content-Disposition'] = 'attachment; filename=' . urlencode($pFileName);
            
            $f = tmpfile();
            fwrite($f, $pContent);
            $fstat = fstat($f);
            $pHeaders['Content-Length'] = $fstat['size'];
            fclose($f);
        }
        
        $response = static::create($pContent, $pStatus, $pProtocol, $pHeaders);
        
        if($file)
        {
            $response->send();
            exit();
        }
        
        return $response;
    }
}