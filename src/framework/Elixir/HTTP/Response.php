<?php

namespace Elixir\HTTP;

use Elixir\HTTP\Headers;
use Elixir\HTTP\Request;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Response 
{
    /**
     * @param string $pValue
     * @return Response
     * @throws \InvalidArgumentException
     */
    public static function fromString($pValue)
    {
        $errorText = 'This response text is not valid.';
        $lines = explode("\r\n", $pValue);
        
        if(false === $lines || count($lines) == 0)
        {
            throw new \InvalidArgumentException($errorText);
        }
        
        $line = array_shift($lines);
        
        if(!preg_match('/^(?P<protocol>HTTP\/1\.(0|1)) (?P<status>\d{3}).*$/', $line, $matches))
        {
            throw new \InvalidArgumentException($errorText);
        }
        
        $protocol = $matches['protocol'];
        $status = $matches['status'];
        $headers = array();
        $content = array();
        $type = 'header';
        
        while(count($lines) > 0)
        {
            $line = array_shift($lines);
            
            if($type == 'header' && '' == $line)
            {
                $type = 'content';
                continue;
            }
            
            $type == 'header' ? $headers[] = $line : $content[] = $line;
        }
        
        return new static(
            implode("\r\n", $content),
            $status,
            $protocol,
            $headers
        );
    }
    
    /**
     * @var array 
     */
    public static $statusCodesAndReasonPhrases = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Statust',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    );
    
    /**
     * @var integer
     */
    protected $_statusCode = 200;
    
    /**
     * @var string
     */
    protected $_protocol = 'HTTP/1.1';
    
    /**
     * @var string
     */
    protected $_content = '';
    
    /**
     * @var Headers
     */
    protected $_headers;
    
    /**
     * @var boolean
     */
    protected $_send = false;
    
    /**
     * @var Request
     */
    protected $_request;

    /**
     * @param string $pContent
     * @param integer $pStatus
     * @param string $pProtocol
     * @param array $pHeaders
     */
    public function __construct($pContent = '',
                                $pStatus = 200,
                                $pProtocol = 'HTTP/1.1',
                                array $pHeaders = array()) 
    {
        $this->setContent($pContent);
        $this->setStatusCode($pStatus);
        $this->setProtocol($pProtocol);
        
        $this->_headers = new Headers();
        $this->_headers->sets($pHeaders);
    }
    
    /**
     * @param Request $pValue
     */
    public function setRequest(Request $pValue)
    {
        $this->_request = $pValue;
        $this->setProtocol($this->_request->getServer('SERVER_PROTOCOL', 'HTTP/1.1'));
    }
    
    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @param integer $pValue
     * @throws \InvalidArgumentException
     */
    public function setStatusCode($pValue)
    {
        if(!isset(static::$statusCodesAndReasonPhrases[$pValue]))
        {
            throw new \InvalidArgumentException(sprintf('The status code "%s" is invalid.', $pValue));
        }
        
        $this->_statusCode = $pValue;
    }
    
    /**
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }
    
    /**
     * @return string
     */
    public function getReasonPhrase()
    {
        return static::$statusCodesAndReasonPhrases[$this->_statusCode];
    }
    
    /**
     * @param string $pValue
     * @throws \InvalidArgumentException
     */
    public function setProtocol($pValue)
    {
        if(!in_array($pValue, array('HTTP/1.0', 'HTTP/1.1')))
        {
            throw new \InvalidArgumentException(sprintf('The protocol "%s" is invalid.', $pValue));
        }
        
        $this->_protocol = $pValue;
    }
    
    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->_protocol;
    }
    
    /**
     * @param string $pValue
     */
    public function setContent($pValue)
    {
        $this->_content = $pValue;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }
    
    /**
     * @return Headers
     */
    public function getHeaders()
    {
        return $this->_headers;
    }
    
    /**
     * @return boolean
     */
    public function isOk()
    {
        return 200 == $this->_statusCode;
    }
    
    /**
     * @return boolean
     */
    public function isNotFound()
    {
        return 404 == $this->_statusCode;
    }
    
    /**
     * @return boolean
     */
    public function isForbidden()
    {
        return 403 == $this->_statusCode;
    }
    
    /**
     * @return boolean
     */
    public function isInformational()
    {
        return $this->_statusCode >= 100 && $this->_statusCode < 200;
    }
    
    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->_statusCode >= 200 && $this->_statusCode < 300;
    }
    
    /**
     * @return boolean
     */
    public function isRedirection()
    {
        return $this->_statusCode >= 300 && $this->_statusCode < 400;
    }
    
    /**
     * @return boolean
     */
    public function isClientError()
    {
        return $this->_statusCode >= 400 && $this->_statusCode < 500;
    }
    
    /**
     * @return boolean
     */
    public function isServerError()
    {
        return $this->_statusCode >= 500 && $this->_statusCode < 600;
    }
    
    /**
     * @param Request $pRequest
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function isNotModified(Request $pRequest = null)
    {
        if(null !== $pRequest)
        {
            $this->setRequest($pRequest);
        }
        
        if(null === $this->_request)
        {
            throw new \InvalidArgumentException('Request is not defined.');
        }
        
        $lastModified = $this->_headers->get('Last-Modified');
        $etag = $this->_headers->get('Etag');
        
        if(null !== $this->_request->getHeaders())
        {
            $ifModifiedSince = $this->_request->getHeaders('If-Modified-Since');
            $ifNoneMatch = $this->_request->getHeaders('If-None-Match');
        }
        else
        {
            $ifModifiedSince = $this->_request->getServer('HTTP_IF_MODIFIED_SINCE');
            $ifNoneMatch = $this->_request->getServer('HTTP_IF_NONE_MATCH');
        }
        
        if(null !== $lastModified && null !== $ifModifiedSince)
        {
            if($lastModified == strtotime($ifModifiedSince))
            {
                return true;
            }
        }
        
        if(null !== $etag && null !== $ifNoneMatch)
        {
            if($etag == $ifNoneMatch)
            {
                return true;
            }
        }
        
        return false;
    }

    public function optimizeHeaders()
    {
        if($this->_headers->has('Location') && $this->isOk())
        {
            $this->setStatusCode(302);
        }
        
        if ($this->_statusCode != 304 && $this->_statusCode != 204)
        {
            if(!$this->_headers->has('Content-Type'))
            {
                $this->_headers->set('Content-Type', 'text/html; charset=UTF-8');
            }
            else
            {
                $contentType = $this->_headers->get('Content-Type');

                if(0 === strpos($contentType, 'text/') && false === strpos($contentType, 'charset'))
                {
                    $this->_headers->set('Content-Type', $contentType . '; charset=UTF-8');
                }
            }
        }
        else
        {
            $this->setContent('');
        }
        
        foreach($this->_headers->gets() as $key => $value)
        {
            if(preg_match('/^HTTP\/1\.(0|1) \d{3}.*$/', $key))
            {
                $this->_headers->remove($key);
            }
        }
        
        $protocol = $this->getProtocol();
        
        if('HTTP/1.0' == $protocol && false !== strpos('no-cache', $this->_headers->get('Cache-Control')))
        {
            $this->_headers->set('Expires', -1);
            $this->_headers->set('Pragma', 'no-cache');
        }
        
        $this->_headers->set(
            sprintf(
                '%s %s %s',
                $protocol,
                $this->_statusCode,
                $this->getReasonPhrase()
            )
        );
        
        if(null === $this->_request)
        {
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            $isSecure = false;
            
            if(isset($_SERVER['HTTPS']))
            {
                $isSecure = (strtoupper($_SERVER['HTTPS']) == 'ON' || $_SERVER['HTTPS'] == 1) ? true : false;
            }
        }
        else
        {
            $userAgent = $this->_request->getServer('HTTP_USER_AGENT');
            $isSecure = $this->_request->isSecure();
        }
        
        // Remove Cache-Control for SSL encrypted downloads when using IE < 9 (http://support.microsoft.com/kb/323308)
        if (false !== stripos($this->_headers->get('Content-Disposition'), 'attachment') && 
            preg_match('/MSIE (.*?);/i', $userAgent, $match) == 1 && 
            $isSecure)
        {
            if (intval(preg_replace('/(MSIE )(.*?);/', '$2', $match[0])) < 9) 
            {
                $this->_headers->remove('Cache-Control');
            }
        }
    }
    
    /**
     * @return boolean
     */
    public function isSent()
    {
        return $this->_send === true;
    }

    /**
     * @return boolean
     */
    public function send()
    {
        $this->optimizeHeaders();
        $this->_headers->send();
                
        echo $this->_content;
        
        $this->_send = true;
        return true;
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        $this->optimizeHeaders();
        return $this->_headers . "\r\n\r\n" . $this->getContent();
    }
}