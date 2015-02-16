<?php

namespace Elixir\Dispatcher;

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */
class Event 
{
    /**
     * @var string
     */
    const START = 'start';

    /**
     * @var string
     */
    const CHANGE = 'change';

    /**
     * @var string
     */
    const COMPLETE = 'complete';

    /**
     * @var string
     */
    const CLOSE = 'close';

    /**
     * @var string
     */
    const ERROR = 'error';

    /**
     * @var string
     */
    const UPDATE = 'update';

    /**
     * @var string
     */
    const CANCEL = 'cancel';

    /**
     * @var string
     */
    const DESTROY = 'destroy';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $target;

    /**
     * @var boolean
     */
    protected $stopPropagation = false;

    /**
     * @param string $type 
     */
    public function __construct($type) 
    {
        $this->type = $type;
    }

    /**
     * @return string 
     */
    public function getType() 
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getTarget() 
    {
        return $this->target;
    }

    /**
     * @param mixed $value 
     */
    public function setTarget($value) 
    {
        $this->target = $value;
    }

    /**
     * @return boolean 
     */
    public function isStopped() 
    {
        return $this->stopPropagation;
    }

    public function stopPropagation() 
    {
        $this->stopPropagation = true;
    }
}
