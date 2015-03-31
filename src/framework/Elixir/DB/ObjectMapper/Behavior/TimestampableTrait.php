<?php

namespace Elixir\DB\ObjectMapper\Model\Behavior;

use Elixir\DB\ObjectMapper\EntityEvent;
use Elixir\DB\ObjectMapper\RepositoryEvent;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
trait TimestampableTrait
{
    /**
     * @var string 
     */
    protected $dateFormat = 'Y-m-d H:i:s';
    
    public function initTimestampableTrait()
    {
        $this->addListener(EntityEvent::DEFINE_FILLABLE, function(EntityEvent $e)
        {
            $this->create_at = $this->getIgnoreValue();
            $this->update_at = $this->getIgnoreValue();
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
     * @param string $value
     */
    public function setDateFormat($value)
    {
        $this->dateFormat = $value;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * @param boolean $save
     * @return boolean
     */
    public function touch($save = false) 
    {
        if ($this->create_at === $this->getIgnoreValue()) 
        {
            $this->create_at = date($this->getDateFormat());
            $this->update_at = $this->create_at;
        } 
        else 
        {
            $this->update_at = date($this->getDateFormat());
        }

        if ($save) 
        {
            return $this->save();
        }

        return true;
    }
}
