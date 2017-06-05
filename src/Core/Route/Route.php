<?php
namespace Duktig\Core\Route;

use Duktig\Core\Route\RouteInterface;

class Route implements RouteInterface
{
    /**
     * @var string $name
     */
    protected $name;
    
    /**
     * @var array $paramsDefaults
     */
    protected $paramsDefaults;
    
    /**
     * @var array $methods
     */
    protected $methods;
    
    /**
     * @var string $path
     */
    protected $path;
    
    /**
     * @var array $paramsRequirements
     */
    protected $paramsRequirements;
    
    /**
     * @var string|callable $handler
     */
    protected $handler;
    
    /**
     * @var string $handlerMethod
     */
    protected $handlerMethod;
    
    /**
     * @param string            $name                   Route name
     * @param array             $methods                [optional] Route HTTP methods
     * @param string            $path                   Route path
     * @param array             $params_defaults        [optional] Default route parameters
     * @param array             $params_requirements    [optional] Key is param name string, and value is
     *                                                  regex expression
     * @param string|callable   $handler                Fully qualified handler class name or a callable
     * @param string            $handlerMethod          [optional] If $handler is a string, this param
     *                                                  should specify the method
     * @throws \InvalidArgumentException
     */
    public function __construct(string $name, array $methods = [], string $path,
        array $paramsDefaults = [], array $paramsRequirements = [], $handler, 
        $handlerMethod = null)
    {
        $this->setName($name);
        $this->setMethods($methods);
        $this->setPath($path);
        $this->setParamsDefaults($paramsDefaults);
        $this->setParamsRequirements($paramsRequirements);
        $this->setHandler($handler, $handlerMethod);
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Route\RouteInterface::setName()
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Route\RouteInterface::getName()
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Route\RouteInterface::setMethods()
     */
    public function setMethods(array $methods = []) : void
    {
        $this->methods = $methods;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Route\RouteInterface::getMethods()
     */
    public function getMethods() : array
    {
        return $this->methods;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Route\RouteInterface::setPath()
     */
    public function setPath(string $path) : void
    {
        $this->path = $path;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Route\RouteInterface::getPath()
     */
    public function getPath() : string
    {
        return $this->path;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Route\RouteInterface::setParamsDefaults()
     */
    public function setParamsDefaults(array $paramsDefaults = []) : void
    {
        $this->paramsDefaults = $paramsDefaults;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Route\RouteInterface::getParamsDefaults()
     */
    public function getParamsDefaults() : array
    {
        return $this->paramsDefaults;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Route\RouteInterface::setParamsRequirements()
     */
    public function setParamsRequirements(array $paramsRequirements = []) : void
    {
        $this->paramsRequirements = $paramsRequirements;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Route\RouteInterface::getParamsRequirements()
     */
    public function getParamsRequirements() : array
    {
        return $this->paramsRequirements;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Route\RouteInterface::setHandler()
     */
    public function setHandler($handler, $handlerMethod = null) : void
    {
        if (!is_callable($handler) && is_null($handlerMethod)) {
            throw new \InvalidArgumentException(
                'In case the $handler is not a callable, $handlerMethod must specify a method to be called'
            );
        }
        $this->handler = $handler;
        $this->handlerMethod = $handlerMethod;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Route\RouteInterface::getHandler()
     */
    public function getHandler()
    {
        return $this->handler;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Route\RouteInterface::getHandlerMethod()
     */
    public function getHandlerMethod() : ?string
    {
        return $this->handlerMethod;
    }
}