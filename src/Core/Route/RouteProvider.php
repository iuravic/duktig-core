<?php
namespace Duktig\Core\Route;

use Duktig\Core\Config\ConfigInterface;
use Duktig\Core\Route\RouteInterface;
use Duktig\Core\Route\RouteProviderInterface;
use Duktig\Core\Route\Route;

class RouteProvider implements RouteProviderInterface
{
    /**
     * @var ConfigInterface $config
     */
    protected $config;
    
    /**
     * @var array $routes
     */
    protected $routes = null;
    
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->setRoutes();
    }
    
    /**
     * Sets \Duktig\Core\Route\Route objects from config values
     */
    public function setRoutes() : void
    {
        if (is_null($this->routes)) {
            $this->routes = [];
            $routesConfig = $this->config->getParam('routes') ?? [];
            foreach ($routesConfig as $routeName => $routeArr) {
                $route = new Route(
                    $routeName,
                    $routeArr['methods'],
                    $routeArr['path'],
                    $routeArr['params_defaults'],
                    $routeArr['params_requirements'],
                    $routeArr['handler'],
                    $routeArr['handler_method'] ?? null
                );
                $this->routes[] = $route;
            }
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Route\RouteProviderInterface::getRoutes()
     */
    public function getRoutes() : array
    {
        return $this->routes;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Route\RouteProviderInterface::getRouteFromName()
     */
    public function getRouteFromName(string $routeName) : ?RouteInterface
    {
        foreach ($this->routes as $route) {
            if ($route->getName() == $routeName) {
                return $route;
            }
        }
        return null;
    }
}