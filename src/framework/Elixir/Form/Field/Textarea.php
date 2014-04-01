<?php

namespace Elixir\Form\Field;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Textarea extends FieldAbstract
{
    /**
     * @see FieldAbstract::__construct()
     */
    public function __construct($pName = null)
    {
        parent::__construct($pName);
        $this->_helper = 'textarea';
    }
}