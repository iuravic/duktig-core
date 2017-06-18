<?php
namespace Duktig\Core\Event;

use Duktig\Core\Event\EventInterface;

abstract class EventAbstract implements EventInterface
{
    protected $name;
    
    /**
     * @param string|null $name [optional] If no event name is provided, the
     *      default name will be the fully qualified class name (without the
     *      prefix backslash).
     */
    public function __construct(string $name = null)
    {
        $this->name = $name;
    }
    
    private function getFQCN()
    {
        return (new \ReflectionClass($this))->getName();
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Event\EventInterface::getName()
     */
    public function getName() : string
    {
        if (is_null($this->name)) {
            $this->name = $this->getFQCN();
        }
        return $this->name;
    }
}