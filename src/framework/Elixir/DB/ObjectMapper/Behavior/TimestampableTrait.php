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
     * @var array 
     */
    protected $timestampable = [
        'format' => 'Y-m-d H:i:s',
        'columns' => [
            'create' => 'create_at',
            'update' => 'update_at'
        ]
    ];
    
    public function bootTimestampableTrait()
    {
        $this->addListener(EntityEvent::DEFINE_FILLABLE, function(EntityEvent $e)
        {
            $this->{$this->timestampable['columns']['create']} = $this->getIgnoreValue();
            $this->{$this->timestampable['columns']['update']} = $this->getIgnoreValue();
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
     * @param boolean $save
     * @return boolean
     */
    public function touch($save = false) 
    {
        $create = &$this->{$this->timestampable['columns']['create']};
        $update = &$this->{$this->timestampable['columns']['update']};
        
        if ($create === $this->getIgnoreValue()) 
        {
            $create = date($this->timestampable['format']);
            $update = $create;
        } 
        else 
        {
            $update = date($this->timestampable['format']);
        }

        if ($save) 
        {
            return $this->save();
        }

        return true;
    }
}
