<?php

namespace Elixir\DB\ORM;

use Elixir\DI\ContainerInterface;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class ModelFactory
{
    /**
     * @param array $pData
     * @param array $pOptions
     * @param ContainerInterface $pManager
     * @return ModelAbstract
     * @throws \InvalidArgumentException
     */
    public static function create(array $pData, array $pOptions = ['raw' => true, 'sync' => true], ContainerInterface $pManager = null)
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
        
        $model->hydrate($pData, $pOptions);
        return $model;
    }
}