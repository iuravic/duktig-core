<?php
namespace Duktig\Core\Event;

use Duktig\Core\Event\EventInterface;

abstract class EventAbstract implements EventInterface
{
    protected $name;
    
    /**
     * @param string|null $name [optional] If no event name is provided, the
     *      default name is its fully qualified class name (without the prefix 
     *      backslash).
     */
    public function __construct(string $name = null)
    {
        if (is_null($name)) {
            $name = (new \ReflectionClass($this))->getName();
        }
        $this->name = $name;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Event\EventInterface::getName()
     */
    public function getName() : string
    {
        return $this->name;
    }
}