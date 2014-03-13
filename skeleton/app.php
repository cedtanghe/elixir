<?php

require_once __DIR__ . '/application/init.php';

$container = new \Elixir\DI\Container();
$container->set('autoloader', $loader);

$application = new \Elixir\MVC\Application($container);
$application->setControllerResolver(new \Elixir\MVC\Controller\ControllerResolver());

// Register modules
$application->addModule(new \Elixir\Module\Application\Bootstrap());
$application->addModule(new \AppExtend\Bootstrap());
$application->addModule(new [MODULE]\Bootstrap());

// Boot all modules
$application->boot();

$request = \Elixir\HTTP\RequestFactory::create();

$response = $application->handle($request);
$response->send();

$application->terminate($request, $response);