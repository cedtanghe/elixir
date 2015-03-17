<?php

namespace Elixir\MVC\Module;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface SelectedInterface
{
    /**
     * @return boolean
     */
    public function isSelected();
    
    public function selected();
}
