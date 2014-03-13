<?php

namespace Elixir\DB;

/**
 * @author Cédric Tanghe <c.tanghe@peoleo.fr>
 */

class DBFactory
{
    /**
     * @var string
     */
    const PDO_MYSQL = 'pdo_mysql';
    
    /**
     * @var string
     */
    const PDO_SQLITE = 'pdo_sqlite';
    
    /**
     * @param array $pConfig
     * @return DBAbstract|null
     * @throws \RuntimeException
     */
    public static function create(array $pConfig)
    {
        if(substr($pConfig['type'], 0, 3) == 'pdo')
        {
            switch($pConfig['type'])
            {
                case self::PDO_MYSQL:
                    $DNS = 'mysql:dbname=' . $pConfig['dbname'];
                
                    if(isset($pConfig['host']))
                    {
                        $DNS .= ';host=' . $pConfig['host'];
                    }

                    if(isset($pConfig['port']))
                    {
                        $DNS .= ';port=' . $pConfig['port'];
                    }
                break;
                case self::PDO_SQLITE:
                    $DNS = 'sqlite:' . (isset($pConfig['dbname']) ? $pConfig['dbname'] : ':memory:');
                break;
                default:
                    throw new \RuntimeException(sprintf('Driver %s is not implemented.', $pConfig['type']));
                break;
            }

            $username = isset($pConfig['username']) ? $pConfig['username'] : null;
            $password = isset($pConfig['password']) ? $pConfig['password'] : null;
            $options = isset($pConfig['options']) ? $pConfig['options'] : array();

            return new PDO($DNS, $username, $password, $options);
        }
        
        return null;
    }
}
