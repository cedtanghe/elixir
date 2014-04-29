<?php

namespace Elixir\Form\Field;

use Elixir\Form\Field\MultipleAbstract;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
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