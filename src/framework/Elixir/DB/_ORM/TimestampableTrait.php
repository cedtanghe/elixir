<?php

namespace Elixir\DB\ORM;

use Elixir\DB\ORM\ModelEvent;

trait TimestampableTrait 
{
    public function timestampable()
    {
        $this->addListener(ModelEvent::DEFINE_COLUMNS, function(ModelEvent $e)
        {
            $this->create_at = null;
            $this->update_at = null;
        });
        
        $this->addListener(ModelEvent::PRE_INSERT, function(ModelEvent $e)
        {
            $this->create_at = date('Y-m-d H:i:s');
            $this->update_at = $this->create_at;
        });
        
        $this->addListener(ModelEvent::PRE_UPDATE, function(ModelEvent $e)
        {
            $this->update_at = date('Y-m-d H:i:s');
        });
    }
}