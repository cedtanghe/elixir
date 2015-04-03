<?php

namespace Elixir\DB\ObjectMapper\Model\Behavior;

use Elixir\DB\ObjectMapper\RepositoryEvent;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
trait TimestampableTrait
{
    /**
     * @return void
     */
    public function bootTimestampableTrait()
    {
        $this->addListener(RepositoryEvent::DEFINE_FILLABLE, function(RepositoryEvent $e)
        {
            $this->{$this->getCreatedColumn()} = $this->getIgnoreValue();
            $this->{$this->getUpdatedColumn()} = $this->getIgnoreValue();
        });

        $this->addListener(RepositoryEvent::PRE_INSERT, function(RepositoryEvent $e) 
        {
            $this->touch(false);
        });
        
        $this->addListener(RepositoryEvent::PRE_UPDATE, function(RepositoryEvent $e) 
        {
            $this->touch(false);
        });
    }
    
    /**
     * @return string
     */
    public function getCreatedColumn()
    {
        return 'created_at';
    }
    
    /**
     * @return string
     */
    public function getCreatedFormat()
    {
        return 'Y-m-d H:i:s';
    }
    
    /**
     * @return string
     */
    public function getUpdatedColumn()
    {
        return 'updated_at';
    }
    
    /**
     * @return string
     */
    public function getUpdatedFormat()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * @param boolean $save
     * @return boolean
     */
    public function touch($save = false) 
    {
        if ($create === $this->getIgnoreValue()) 
        {
            $this->{$this->getCreatedColumn()} = date($this->getCreatedFormat());
            $this->{$this->getUpdatedColumn()} = $create;
        } 
        else 
        {
            $this->{$this->getUpdatedColumn()} = date($this->getUpdatedFormat());
        }

        if ($save) 
        {
            return $this->save();
        }

        return true;
    }
}
