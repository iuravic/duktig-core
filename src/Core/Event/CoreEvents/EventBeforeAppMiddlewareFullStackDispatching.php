<?php
namespace Duktig\Core\Event\CoreEvents;

use Duktig\Core\Event\EventAbstract;

class EventBeforeAppMiddlewareFullStackDispatching extends EventAbstract
{
    protected $stack;
    
    public function __construct(array $stack)
    {
        parent::__construct();
        $this->stack = $stack;
    }
    
    public function getStack() : array
    {
        return $this->stack;
    }
}