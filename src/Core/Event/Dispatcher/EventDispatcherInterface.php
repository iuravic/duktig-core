<?php
namespace Duktig\Core\Event\Dispatcher;

use Duktig\Core\Event\EventInterface;
use Duktig\Core\DI\ContainerInterface;

/**
 * Defines an interface for an event dispatcher, which also uses a resolver to
 * instantiate the listeners as services.
 */
interface EventDispatcherInterface
{
    /**
     * Gets the container.
     * 
     * @return ContainerInterface
     */
    public function getResolver() : ContainerInterface;
    
    /**
     * Attaches a listener to an event.
     * 
     * @param string $eventName
     * @param sting|callable $listener Listener service id/resolvable class name
     *      or callable
     */
    public function addListener(string $eventName, $listener) : void;
    
    /**
     * Dispatches the event.
     * 
     * @param string $eventName
     * @param EventInterface $event
     */
    public function dispatch(EventInterface $event) : void;
}