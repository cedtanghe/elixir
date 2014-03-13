<?php

define('APPLICATION_PATH', realpath(__DIR__));
define('PUBLIC_PATH', realpath(__DIR__ . '/../'));
define('APPLICATION_ENV', $_SERVER['APPLICATION_ENV']);

set_error_handler(function($pSeverity, $pMessage, $pFilename, $pLine) 
{ 
    throw new \ErrorException($pMessage, 0, $pSeverity, $pFilename, $pLine); 
});

switch(APPLICATION_ENV)
{
    case 'development':
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    break;
    default:
        ini_set('display_errors', '0');
        error_reporting(0);
    break;
}

require_once __DIR__ . '/autoload.php';
