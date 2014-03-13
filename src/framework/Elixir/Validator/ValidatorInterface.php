<?php

namespace Elixir\Validator;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

interface ValidatorInterface
{
    /**
     * @param mixed $pContent
     * @param array $pOptions
     * @return boolean
     */
    public function isValid($pContent, array $pOptions = array());
    
    /**
     * @return boolean
     */
    public function hasError();
    
    /**
     * @return string|array
     */
    public function errors();
}