<?php

namespace Elixir\I18N\Writer;

use Elixir\I18N\I18NInterface;
use Elixir\I18N\Writer\WriterInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

abstract class WriterAbstract implements WriterInterface
{
    /**
     * @var I18NInterface
     */
    protected $_I18N;
    
    /**
     * @param I18NInterface $pI18N
     */
    public function __construct(I18NInterface $pI18N = null)
    {
        $this->_I18N = $pI18N;
    }

    /**
     * @param I18NInterface $pValue
     */
    public function setI18N(I18NInterface $pValue)
    {
        $this->_I18N = $pValue;
    }
    
    /**
     * @return I18NInterface
     */
    public function getI18N()
    {
        return $this->_I18N;
    }
}