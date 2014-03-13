<?php

namespace Elixir\DB\ORM;

use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class ModelFactory
{
    /**
     * @param array $pData
     * @param boolean $pRaw
     * @param ContainerInterface $pManager
     * @return ModelAbstract
     * @throws \InvalidArgumentException
     */
    public static function create(array $pData, $pRaw = true, ContainerInterface $pManager = null)
    {
        if(!isset($pData['_class']))
        {
            throw new \InvalidArgumentException('Key "_class" does not exist.');
        }
        
        $model = new $pData['_class']();
        
        if(null !== $pManager)
        {
            $model->setConnectionManager($pManager);
        }
        
        $model->hydrate($pData, $pRaw);
        return $model;
    }
}