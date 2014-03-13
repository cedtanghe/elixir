<?php

namespace AppExtend\DI;

use Elixir\DI\ContainerInterface;
use Elixir\Module\Application\DI\ServicesFilter as ParentServicesFilter;

class ServicesFilter extends ParentServicesFilter
{
    public function load(ContainerInterface $pContainer) 
    {
        parent::load($pContainer);
    }
}