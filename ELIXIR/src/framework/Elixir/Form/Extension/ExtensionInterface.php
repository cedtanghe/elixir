<?php

namespace Elixir\Form\Extension;

use Elixir\Form\FormInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface ExtensionInterface
{
    /**
     * @param FormInterface $pForm
     */
    public function load(FormInterface $pForm);
    
    public function unload();
}
