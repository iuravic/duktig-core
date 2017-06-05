<?php
namespace Duktig\Core\Route\Router;

use Psr\Http\Message\ServerRequestInterface;
use Duktig\Core\Route\Router\RouterMatch;
use Duktig\Core\Exception\HttpException;

interface RouterInterface
{
    /**
     * Match the request to a RouterMatch object, which holds information about
     * the route and the resolved route params.
     * 
     * @param ServerRequestInterface $request
     * @return RouterMatch $routerMatch
     * @throws HttpException If no route is matched
     */
    public function match(ServerRequestInterface $request) : RouterMatch;
}