<?php

namespace AppExtend\DI;

use Elixir\DI\ContainerInterface;
use Elixir\Module\Application\DI\ServicesValidator as ParentServicesValidator;

class ServicesValidator extends ParentServicesValidator
{
    public function load(ContainerInterface $pContainer) 
    {
        parent::load($pContainer);
    }
}