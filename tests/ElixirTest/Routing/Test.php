<?php

namespace ElixirTest\Routing;

use Elixir\ClassLoader\Loader;
use Elixir\Routing\Router;
use Elixir\Routing\Collection;
use Elixir\Routing\Route;
use Elixir\HTTP\RequestFactory;
use Elixir\Routing\Generator\URLGenerator;
use Elixir\Routing\Generator\QueryGenerator;
use Elixir\Routing\Matcher\URLMatcher;
use Elixir\Routing\RouterEvent;

class Test extends \PHPUnit_Framework_TestCase
{
    protected $_loader;
    
    public function __construct()
    {
        require_once __DIR__ . '/../../../src/framework/Elixir/ClassLoader/Loader.php';
        
        $this->_loader = new Loader();
        $this->_loader->register();
    }
     
    public function testMatchRoutes()
    {
        $collection = new Collection();
        $collection->add('root',
                         new Route('index/{name}',
                                   array(Route::MVC => 'default::index::index'),
                                   array('name' => '[a-z]+')));
        
        $collection->add('register',
                         new Route('register',
                                   array(Route::MVC => 'default::register::login'),
                                   array(Route::SECURE => false,
                                         Route::METHOD => 'GET',
                                         Route::ASSERT => function($pMatch)
                                         {
                                              // No output for tests
                                              //echo "\n";
                                              //echo 'Match called';
                                              //echo "\n";

                                              return true;
                                         },
                                         Route::ATTRIBUTES => true)),
                         999);
        
        $request = RequestFactory::create();
        $request->setBaseURL('http://www.test.fr');
                                          
        $routing = new Router($collection);
        $routing->setRequest($request);
        $routing->addListener(RouterEvent::ROUTE_MATCH, function(RouterEvent $e)
        {
            // No output for tests
            //echo "\n";
            //echo 'Match found => ' . $e->getRouteMatch()->getRouteName();
            //echo "\n";
        });
        
        $routing->setURLMatcher(new URLMatcher());
        
        $this->assertInstanceOf('Elixir\Routing\Matcher\RouteMatch', $routing->match('index/user'));
        $this->assertInstanceOf('Elixir\Routing\Matcher\RouteMatch', $routing->match('register/test/6'));
    }
    
    public function testLoadRoutes()
    {
        $request = RequestFactory::create();
        $request->setBaseURL('http://www.test.fr');
        
        $routing = new Router(new Collection());
        $routing->setRequest($request);
        $routing->load(array(__DIR__ . '/../../config/routes.xml',
                             __DIR__ . '/../../config/routes.json',
                             __DIR__ . '/../../config/routes.php'));
        
        $this->assertEquals(true, $routing->getCollection()->has('xml-route-2'));
        $this->assertEquals(true, $routing->getCollection()->has('php-route-3'));
        $this->assertEquals(true, $routing->getCollection()->has('json-route-4'));
    }
    
    public function testGenerateURL()
    {
        $collection = new Collection();
        $collection->add('root',
                         new Route('index/{name}',
                                   array(Route::MVC => 'default::index::index'),
                                   array('name' => '[a-z]+',
                                         Route::ATTRIBUTES => true)));
        
        $request = RequestFactory::create();
        $request->setBaseURL('http://www.test.fr');
        
        $routing = new Router($collection);
        $routing->setRequest($request);
        $routing->setURLGenerator(new URLGenerator());
        
        $this->assertEquals('index/user', $routing->generate('root', array('name' => 'user'), QueryGenerator::URL_RELATIVE));
        $this->assertEquals('http://www.test.fr/index/user', $routing->generate('root', array('name' => 'user')));
        
        
        $this->assertEquals('index/user?p1=v1&p2=v2', $routing->generate('root', 
                                                                         array('name' => 'user',
                                                                               Route::QUERY => array('p1' => 'v1',
                                                                                                     'p2' => 'v2')),
                                                                         QueryGenerator::URL_RELATIVE));
        
        $this->assertEquals('index/user/other/new%2F8', $routing->generate('root', 
                                                                           array('name' => 'user',
                                                                                 'other' => 'new/8'),
                                                                           QueryGenerator::URL_RELATIVE));
        
        $routing->setURLGenerator(new QueryGenerator());
        
        $this->assertEquals('?r=index/user', $routing->generate('root', array('name' => 'user'), QueryGenerator::URL_RELATIVE));
        $this->assertEquals('http://www.test.fr?r=index/user', $routing->generate('root', array('name' => 'user')));
        
        
        $this->assertEquals('?p1=v1&p2=v2&r=index/user', $routing->generate('root', 
                                                                            array('name' => 'user',
                                                                                  Route::QUERY => array('p1' => 'v1',
                                                                                                                'p2' => 'v2')),
                                                                            QueryGenerator::URL_RELATIVE));

        $this->assertEquals('//www.test.fr?r=index/user/other/new%2F8', $routing->generate('root', 
                                                                                           array('name' => 'user',
                                                                                                 'other' => 'new/8'),
                                                                                           QueryGenerator::SHEMA_RELATIVE));
    }
}
