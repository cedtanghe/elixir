<?php

namespace Elixir\Routing;

use Elixir\Dispatcher\Dispatcher;
use Elixir\HTTP\Request;
use Elixir\Routing\Generator\GeneratorInterface;
use Elixir\Routing\Loader\LoaderFactory;
use Elixir\Routing\Matcher\MatcherInterface;
use Elixir\Routing\Matcher\RouteMatch;
use Elixir\Routing\Writer\WriterInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Router extends Dispatcher implements RouterInterface
{
    /**
     * @var Request 
     */
    protected $_request;
    
    /**
     * @var Collection 
     */
    protected $_collection;
    
    /**
     * @var MatcherInterface 
     */
    protected $_URLMatcher;
    
    /**
     * @var GeneratorInterface 
     */
    protected $_URLGenerator;
    
    /**
     * @param Collection $pCollection
     */
    public function __construct(Collection $pCollection = null)
    {
        if(null !== $pCollection)
        {
            $this->setCollection($pCollection);
        }
    }
    
    /**
     * @param Collection $pValue
     */
    public function setCollection(Collection $pValue)
    {
        $this->_collection = $pValue;
    }
    
    /**
     * @see RouterInterface::getCollection()
     */
    public function getCollection()
    {
        return $this->_collection;
    }
    
    /**
     * @param Request $pValue
     */
    public function setRequest(Request $pValue)
    {
        $this->_request = $pValue;
        
        if(null !== $this->_URLMatcher)
        {
            $this->_URLMatcher->setRequest($this->_request);
        }
        
        if(null !== $this->_URLGenerator)
        {
            $this->_URLGenerator->setRequest($this->_request);
        }
    }
    
    /**
     * @see RouterInterface::getRequest()
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    /**
     * @param MatcherInterface $pValue
     */
    public function setURLMatcher(MatcherInterface $pValue)
    {
        $this->_URLMatcher = $pValue;
        
        if(null !== $this->_request)
        {
            $this->_URLMatcher->setRequest($this->_request);
        }
    }

    /**
     * @return MatcherInterface
     */
    public function getURLMatcher()
    {
        return $this->_URLMatcher;
    }
    
    /**
     * @param GeneratorInterface $pValue
     */
    public function setURLGenerator(GeneratorInterface $pValue)
    {
        $this->_URLGenerator = $pValue;
        
        if(null !== $this->_request)
        {
            $this->_URLGenerator->setRequest($this->_request);
        }
    }

    /**
     * @return GeneratorInterface
     */
    public function getURLGenerator()
    {
        return $this->_URLGenerator;
    }
    
    /**
     * @param mixed $pConfig
     */
    public function load($pConfig)
    {
        if($pConfig instanceof Collection)
        {
            $this->getCollection()->merge($pConfig);
        }
        else
        {
            foreach((array)$pConfig as $config)
            {
                $loader = LoaderFactory::create($config);
                $loader->load($config, $this->getCollection());
            }
        }
    }
    
    /**
     * @param WriterInterface $pWriter
     * @param string $pFile
     * @return boolean
     */
    public function export(WriterInterface $pWriter, $pFile)
    {
        $pWriter->setRouter($this);
        return $pWriter->export($pFile);
    }

    /**
     * @see RouterInterface::match()
     * @throws \RuntimeException
     */
    public function match($pURL = null)
    {
        if(null === $this->_URLMatcher)
        {
            throw new \RuntimeException('URLMatcher class is not defined.');
        }
        
        if(null === $pURL && null === $this->_request)
        {
            throw new \RuntimeException('Request class is not defined.');
        }
        
        $URL = trim($pURL ?: $this->_request->getPathInfo(), '/');
        $match = $this->_URLMatcher->match($this->_collection, $URL);
        
        if($match instanceof RouteMatch)
        {
            $this->dispatch(new RouterEvent(RouterEvent::ROUTE_MATCH, $match));
            return $match;
        }
        
        $this->dispatch(new RouterEvent(RouterEvent::ROUTE_NOT_FOUND));
        return null;
    }
    
    /**
     * @see RouterInterface::generate()
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function generate($pName, array $pOptions = array(), $pMode = GeneratorInterface::URL_ABSOLUTE)
    {
        if(null === $this->_URLGenerator)
        {
            throw new \RuntimeException('URLGenerator class is not defined.');
        }
        
        $route = $this->_collection->get($pName);
        
        if(null === $route)
        {
            throw new \InvalidArgumentException(sprintf('Route "%s" does not exist.', $pName));
        }
        
        return $this->_URLGenerator->generate($route, $pOptions, $pMode);
    }
}
