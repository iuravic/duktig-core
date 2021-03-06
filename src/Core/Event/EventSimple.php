<?php
namespace Duktig\Core\Event;

use Duktig\Core\Event\EventAbstract;

/**
 * This event class can be used to instantiate simple event objects on the run
 * which have no special state, and just use the event name as their ID.
 */
class EventSimple extends EventAbstract
{
    public function __construct(string $name)
    {
        parent::__construct($name);
    }
}