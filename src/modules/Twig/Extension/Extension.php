<?php

namespace Elixir\Module\Twig\Extension;

use Elixir\Module\Twig\Extension\Helper;
use Elixir\Module\Twig\View\Twig;
use Twig_Extension;
use Twig_SimpleFunction;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Extension extends Twig_Extension
{
    /**
     * @var Twig
     */
    protected $_view;
    
    /**
     * @param Twig $pView
     */
    public function __construct(Twig $pView)
    {
        $this->_view = $pView;
    }
    
    /**
     * @see Twig_Extension::getName()
     */
    public function getName()
    {
        return 'elixir_extension';
    }
    
    /**
     * @see Twig_Extension::getGlobals()
     */
    public function getGlobals()
    {
        return [
            'helper' => new Helper($this->_view),
        ];
    }
    
    /**
     * @see Twig_Extension::getFunctions()
     */
    public function getFunctions() 
    {
        return [
            new Twig_SimpleFunction(
                'filter_*' ,
                function($pMethod)
                {
                    $args = func_get_args();
                    array_shift($args);
                    
                    return call_user_func_array(['\Elixir\Facade\Filter', $pMethod], $args);
                }
            ),
            new Twig_SimpleFunction(
                'validator_*' ,
                function($pMethod)
                {
                    $args = func_get_args();
                    array_shift($args);
                    
                    return call_user_func_array(['\Elixir\Facade\Validator', $pMethod], $args);
                }
            )
        ];
    }
}