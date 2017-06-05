<?php
namespace Duktig\Core\Route\Router;

use Duktig\Core\Route\Route;
use Duktig\Core\Route\RouteInterface;

class RouterMatch
{
    protected $route;
    protected $routeParams;
    
    /**
     * @param Route $route
     * @param array $routeParams [optional]
     */
    public function __construct(Route $route, array $routeParams = [])
    {
        $this->route = $route;
        $this->routeParams = $routeParams;
    }
    
    /**
     * @return RouteInterface
     */
    public function getMatchedRoute() : RouteInterface
    {
        return $this->route;
    }
    
    /**
     * @return array
     */
    public function getMatchedRouteParams() : array
    {
        return $this->routeParams;
    }
}