<?php

namespace Elixir\HTTP\Session\SaveHandler;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

interface SaveHandlerInterface 
{
    /**
     * @param string $pSavePath
     * @param string $pName
     * @return boolean
     */
    public function open($pSavePath, $pName);
    
    /**
     * @return boolean
     */
    public function close();
    
    /**
     * @param string $pId
     * @return boolean
     */
    public function read($pId);
    
    /**
     * @param string $pId
     * @param string $pData
     * @return boolean
     */
    public function write($pId, $pData);
    
    /**
     * @param string $pId
     * @return boolean
     */
    public function destroy($pId);
    
    /**
     * @param integer $pMaxLifetime
     */
    public function gc($pMaxLifetime);
}