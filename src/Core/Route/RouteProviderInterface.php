<?php
namespace Duktig\Core\Route;

interface RouteProviderInterface
{
    /**
     * Gets all registered routes.
     * 
     * @return array An array of \Duktig\Core\Route\Route objects
     */
    public function getRoutes() : array;
    
    /**
     * Gets a route by its name.
     * 
     * @param string $routeName
     * @return \Duktig\Core\Route\RouteInterface|NULL
     */
    public function getRouteFromName(string $routeName) : ?RouteInterface;
}