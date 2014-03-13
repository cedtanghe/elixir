<?php

// Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Standard autoloader
$loader = new \Elixir\ClassLoader\Loader();
$loader->register();

$loader->addNamespace('AppExtend', __DIR__ . '/modules/AppExtend/');
$loader->addNamespace('[MODULE]', __DIR__ . '/modules/[MODULE]/');
