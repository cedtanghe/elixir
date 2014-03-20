<?php

namespace Elixir\View;

use Elixir\Filter\FilterInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface EscaperInterface
{
    /**
     * @param boolean $pValue
     */
    public function setAutoEscape($pValue);
    
    /**
     * @return boolean
     */
    public function isAutoEscape();
    
    /**
     * @param FilterInterface $pValue
     */
    public function setEscaper(FilterInterface $pValue);
    
    /**
     * @return FilterInterface
     */
    public function getEscaper();

    /**
     * @param mixed $pData
     * @param string $pStrategy
     * @return mixed
     */
    public function escape($pData, $pStrategy = 'html');
    
    /**
     * @param string $pKey
     * @param mixed $pDefault
     * @return mixed
     */
    public function raw($pKey, $pDefault = null);
}