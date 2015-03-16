<?php

namespace Elixir\DB\ORM\Model;

use Elixir\DB\ORM\EntityEvent;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
trait TimestampableTrait
{
    /**
     * @var string 
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    public function timestampable()
    {
        $this->addListener(EntityEvent::DEFINE_COLUMNS, function(EntityEvent $e)
        {
            $this->create_at = $this->getIgnoreValue();
            $this->update_at = $this->getIgnoreValue();
        });

        $this->addListener(EntityEvent::PRE_INSERT, function(EntityEvent $e) 
        {
            $this->touch();
        });

        $this->addListener(EntityEvent::PRE_UPDATE, function(EntityEvent $e) 
        {
            $this->touch();
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
     * @param boolean $update
     * @return boolean
     */
    public function touch($update = true) 
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

        if ($update) 
        {
            return $this->update();
        }

        return true;
    }
}
