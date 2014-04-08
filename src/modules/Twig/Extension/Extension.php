<?php

namespace Elixir\Module\Twig\Extension;

use Elixir\Module\Twig\View\Helper;
use Elixir\Module\Twig\View\Twig;
use Twig_Extension;

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
        return array(
            'helper' => new Helper($this->_view),
        );
    }
}