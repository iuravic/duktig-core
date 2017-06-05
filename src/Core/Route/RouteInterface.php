<?php
namespace Duktig\Core\Route;

interface RouteInterface
{
    /**
     * @param string $name Route name
     */
    public function setName(string $name) : void;
    
    /**
     * @return string $name Route name
     */
    public function getName() : string;
    
    /**
     * @param array $paramsDefaults Route parameters default values
     */
    public function setParamsDefaults(array $paramsDefaults) : void;
    
    /**
     * @return array $paramsDefaults
     */
    public function getParamsDefaults() : array;

    /**
     * @param array $methods Route HTTP methods
     */
    public function setMethods(array $methods) : void;
    
    /**
     * @return array $methods
     */
    public function getMethods() : array;

    /**
     * @param string $path Route path
     */
    public function setPath(string $path);
    
    /**
     * @return string $path Route path
     */
    public function getPath() : string;

    /**
     * @param array $params_requirements Key is param name string, and value is
     *  regex expression
     */
    public function setParamsRequirements(array $paramsRequirements) : void;
    
    /**
     * @return array $params_requirements
     */
    public function getParamsRequirements() : array;
    
    /**
     * @param string|callable   $handler        Fully qualified handler class name or 
     *                                          a callable.
     * @param string|null       $handlerMethod  [optional] If $handler is a string, 
     *                                          this param should specify the method.
     * @throws \InvalidArgumentException
     */
    public function setHandler($handler, $handlerMethod = null) : void;
    
    /**
     * @return string|callable $handler Fully qualified handler class name or a callable
     */
    public function getHandler();
    
    /**
     * @return string|null $handlerMethod If $handler is a string, this param should 
     *  specify the method
     */
    public function getHandlerMethod() : ?string;
}