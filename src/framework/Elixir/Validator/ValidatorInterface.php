<?php

namespace Elixir\Validator;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface ValidatorInterface
{
    /**
     * @param mixed $pContent
     * @param array $pOptions
     * @return boolean
     */
    public function isValid($pContent, array $pOptions = []);
    
    /**
     * @return boolean
     */
    public function hasError();
    
    /**
     * @return string|array
     */
    public function errors();
}
