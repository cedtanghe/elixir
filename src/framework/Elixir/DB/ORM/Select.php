<?php

namespace Elixir\DB\ORM;

use Elixir\DB\DBInterface;
use Elixir\DB\ORM\RepositoryInterface;
use Elixir\DB\Result\SetAbstract;
use Elixir\DB\SQL\Select as SQLSelect;
use Elixir\Util\Str;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class Select
{
    /**
     * @var array 
     */
    protected $_loads = [];
    
    /**
     * @var RepositoryInterface
     */
    protected $_repository;
    
    /**
     * @var DBInterface
     */
    protected $_DB;
    
    /**
     * @var SQLSelect
     */
    protected $_SQL;
    
    /**
     * @param RepositoryInterface $pRepository
     * @param string $pAlias
     */
    public function __construct(RepositoryInterface $pRepository, $pAlias = null) 
    {
        $this->_repository = $pRepository;
        $this->_DB = $this->_repository->getConnection('db.read');
        
        $table = '`' . $this->_repository->getTable() . '`';
        
        if(null !== $pAlias)
        {
            $table .= ' AS `' . $pAlias . '`';
        }
        
        $this->_SQL = $this->_DB->createSelect($table);
    }
    
    /**
     * @see SQLSelect::table()
     * @return Select
     * @throws \LogicException
     */
    public function table($pTable)
    {
        if(false === strpos($pTable, $this->_repository->getTable()))
        {
            throw new \LogicException(sprintf('The table is closely related to repository "%s".', get_class($this)));
        }
        
        $this->_SQL->table($pTable);
        return $this;
    }
    
    /**
     * @return Select
     */
    public function current()
    {
        $keys = (array)$this->_repository->getPrimaryKey();
        $values = (array)$this->_repository->getPrimaryValue();
        $c = 0;
        
        foreach($keys as $primary)
        {
            $this->_SQL->where('`' . $this->_repository->getTable() . '`.`' . $primary . '` = ?', $values[$c]);
            $c++;
        }
        
        return $this;
    }
    
    /**
     * @param integer|array $pId
     * @return Select
     * @throws \LogicException
     */
    public function primary($pId)
    {
        if(is_array($this->_repository->getPrimaryKey()))
        {
            throw new \LogicException('It is impossible to do a search if multiple primary keys are defined.');
        }

        $this->_SQL->where('`' . $this->_repository->getTable() . '`.`' . $this->_repository->getPrimaryKey() . '` IN(?)', (array)$pId);
        return $this;
    }

    /**
     * @param string $pMethod
     * @return Select
     */
    public function scope($pMethod)
    {
        $options = [];
        
        if(func_num_args() > 1)
        {
            $args = func_get_args();
            
            array_shift($args);
            $options = $args;
        }
        
        array_unshift($options, $this);
        call_user_func_array([$this->_repository, 'scope' . Str::camelize($pMethod)], $options);
        
        return $this;
    }

    /**
     * @param string $pMethod
     * @return Select
     */
    public function load($pMethod)
    {
        $options = [];
        
        if(func_num_args() > 1)
        {
            $args = func_get_args();
            
            array_shift($args);
            $options = $args;
        }
        
        $this->_loads[$pMethod] = $options;
        return $this;
    }
    
    /**
     * @see SQLSelect::reset()
     * @return Select
     */
    public function reset($pPart)
    {
        switch($pPart)
        {
            case 'loads':
                $this->_loads = [];
            break;
        }
        
        $this->_SQL->reset($pPart);
        return $this;
    }
    
    /**
     * @return boolean
     */
    public function has()
    {
        return $this->count() > 0;
    }
    
    /**
     * @return integer
     */
    public function count()
    {
        $SQL = clone $this->_SQL;
        
        if(false === strpos($SQL->render(), 'COUNT('))
        {
            $SQL->columns('COUNT(*)', true);
        }
        
        if(false === strpos($SQL->render(), 'GROUP BY'))
        {
            return (int)$this->_DB->query($SQL)->fetchColumn(0);
        }
        else
        {
            return count($this->raw());
        }
    }
    
    /**
     * @return array
     */
    public function raw()
    {
        $result = $this->_DB->query($this->_SQL);
        return $result->fetchAll(SetAbstract::FETCH_ASSOC);
    }
    
    /**
     * @return RepositoryInterface|null
     */
    public function one()
    {
        $this->_SQL->limit(1);
        $repositories = $this->all();
        
        return count($repositories) > 0 ? $repositories[0] : null;
    }
    
    /**
     * @return array
     */
    public function all()
    {
        $rows = $this->raw();
        $repositories = [];
        $class = get_class($this->_repository);
        
        foreach($rows as $row)
        {
            $repository = new $class($this->_repository->getConnectionManager());
            $repository->hydrate($row, ['raw' => true, 'sync' => true]);
            
            $this->extend($repository);
            $repositories[] = $repository;
        }
        
        return $repositories;
    }
    
    /**
     * @param RepositoryInterface $pRepository
     */
    protected function extend(RepositoryInterface $pRepository)
    {
        foreach($this->_loads as $key => $value)
        {
            $method = 'load' . Str::camelize($key);
            
            if(method_exists($pRepository, $method))
            {
                call_user_func_array([$pRepository, $method], $value);
            }
            else
            {
                // Use lazy loading
                $pRepository->$key;
            }
        }
    }
    
    /**
     * @param string $pMethod
     * @param array $pArguments
     * @return Select
     */
    public function __call($pMethod, $pArguments) 
    {
        $result = call_user_func_array([$this->_SQL, $pMethod], $pArguments);
        
        if($result instanceof SQLSelect)
        {
            return $this;
        }
        
        return $result;
    }

    /**
     * @return string
     */
    public function __toString() 
    {
        return $this->_SQL->render();
    }
    
    public function __clone() 
    {
        $this->_SQL = clone $this->_SQL;
    }
}
