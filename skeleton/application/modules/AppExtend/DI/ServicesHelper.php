<?php

namespace AppExtend\DI;

use Elixir\DI\ContainerInterface;
use Elixir\Module\Application\DI\ServicesHelper as ParentServicesHelper;

class ServicesHelper extends ParentServicesHelper
{
    public function load(ContainerInterface $pContainer) 
    {
        parent::load($pContainer);
    }
}