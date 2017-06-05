<?php
namespace Duktig\Core\Event;

/**
 * An event is simply a value object whose classes implement this interface.
 * When an event is triggered, it is passed to it's listeners, which then use it.
 * 
 * Events are identified by their unique names.
 */
interface EventInterface
{
    /**
     * Gets the event's name.
     * 
     * @return string
     */
    public function getName() : string;
}