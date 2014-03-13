<?php

namespace Elixir\Form\Field;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class Select extends MultipleAbstract
{
    /**
     * @see MultipleAbstract::__construct()
     */
    public function __construct($pName = null)
    {
        parent::__construct($pName);
        $this->_helper = 'select';
    }
}