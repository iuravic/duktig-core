<?php
namespace Duktig\Core\DI;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Define an alias for a type/service ID.
     * 
     * @param string $original
     * @param string $alias
     */
    public function alias($original, $alias) : void;
    
    /**
     * Define a factory to create an instance of the $name.
     * 
     * @param string $name
     * @param callable|string $callableOrMethodStr
     */
    public function factory($name, $callableOrMethodStr) : void;
    
    /**
     * Define service as a shared singleton.
     * 
     * @param unknown $nameOrInstance
     */
    public function singleton($nameOrInstance) : void;
    
    /**
     * Resolve a previously unregistered closure as a service and return its
     * response.
     *
     * @param \Closure $closureUnresolved
     * @return mixed Result from the closure
     */
    public function resolveClosure(\Closure $closureUnresolved);
}