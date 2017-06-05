<?php
namespace Duktig\Core\Event;

use Duktig\Core\Event\EventInterface;

interface ListenerInterface
{
    /**
     * This method is called when the listener's event is dispatched.
     * 
     * @param EventInterface $event
     */
    public function handle(EventInterface $event) : void;
}