<?php

namespace Elixir\HTTP\Session\Handler;

use Elixir\DB\DBInterface;
use Elixir\DB\Result\SetAbstract;
use Elixir\DB\SQL\Insert;
use Elixir\DB\SQL\Update;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class DB implements \SessionHandlerInterface
{
    /**
     * @var DBInterface 
     */
    protected $_DB;
    
    /**
     * @var integer 
     */
    protected $_lifeTime;
    
    /**
     * @param DBInterface $pDB
     * @param integer $pLifeTime
     */
    public function __construct(DBInterface $pDB, $pLifeTime = -1) 
    {
        $this->_DB = $pDB;
        
        if($pLifeTime != -1)
        {
            $this->_lifeTime = $pLifeTime;
            ini_set('session.gc_maxlifetime', $this->_lifeTime);
        }
        else
        {
            $this->_lifeTime = ini_get('session.gc_maxlifetime');
        }
    }
    
    /**
     * @return DBInterface
     */
    public function getDB()
    {
        return $this->_DB;
    }
    
    /**
     * @return integer
     */
    public function getLifeTime()
    {
        return $this->_lifeTime;
    }

    /**
     * @see \SessionHandlerInterface::open()
     */
    public function open($pSavePath, $pName)
    {
        return true;
    }
    
    /**
     * @see \SessionHandlerInterface::close()
     */
    public function close()
    {
        $this->gc($this->_lifeTime);
        return true;
    }
    
    /**
     * @see \SessionHandlerInterface::read()
     */
    public function read($pId)
    {
        $select = $this->_DB->createSelect('`sessions`')
                  ->columns('`data`')
                  ->where('`id` = ?', $pId)
                  ->where('`expires` > ?', time());
        
        $result = $this->_DB->query($select);
        $row = $result->fetch(SetAbstract::FETCH_ASSOC);
        
        if(false !== $row)
        {
            return $row['data'];
        }
        
        return '';
    }
    
    /**
     * @see \SessionHandlerInterface::write()
     */
    public function write($pId, $pData)
    {
        $life = time() + $this->_lifeTime;
        
        $select = $this->_DB->createSelect('`sessions`')
                  ->columns('COUNT(*)')
                  ->where('`id` = ?', $pId);
        
        $result = $this->_DB->query($select);
        
        if((int)$result->fetchColumn(0) > 0)
        {
            $update = $this->_DB->createUpdate('`sessions`')
                      ->set(['`expires`' => $life, '`data`' => $pData], Update::VALUES_SET)
                      ->where('`id` = ?', $pId);
            
            $result = $this->_DB->query($update);
        }
        else
        {
            $insert = $this->_DB->createInsert('`sessions`')
                      ->values(
                          [
                              '`id`' => $pId,
                              '`expires`' => $life,
                              '`data`' => $pData
                          ],
                          Insert::VALUES_SET
                      );
            
            $result = $this->_DB->query($insert);
        }
        
        return $result->rowCount() > 0;
    }
    
    /**
     * @see \SessionHandlerInterface::destroy()
     */
    public function destroy($pId)
    {
        $delete = $this->_DB->createDelete('`sessions`')->where('`id` = ?', $pId);
        $result = $this->_DB->query($delete);
        
        return $result->rowCount() > 0;
    }
    
    /**
     * @see \SessionHandlerInterface::gc()
     */
    public function gc($pMaxLifetime)
    {
        $delete = $this->_DB->createDelete('`sessions`')->where('`expires` < ?', time() - (int)$pMaxLifetime);
        $result = $this->_DB->query($delete);
        
        return $result->rowCount() > 0;
    }
}