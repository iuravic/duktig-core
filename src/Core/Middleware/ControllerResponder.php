<?php
namespace Duktig\Core\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Duktig\Core\Event\Dispatcher\EventDispatcherInterface;
use Duktig\Core\DI\ContainerInterface;
use Duktig\Core\Route\Router\RouterMatch;
use Psr\Http\Message\ResponseInterface;
use Duktig\Core\Event\CoreEvents\{
    EventBeforeAppRouteHandling,
    EventAfterAppRouteHandling
};

class ControllerResponder implements MiddlewareInterface
{
    protected $container;
    protected $eventDispatcher;
    protected $routerMatch;
    
    public function __construct(ContainerInterface $container,
        EventDispatcherInterface $eventDispatcher)
    {
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
    }
    
    /**
     * Set the RouterMatch parameter.
     * 
     * @param RouterMatch $routerMatch
     */
    public function setRouterMatch(RouterMatch $routerMatch) : void
    {
        $this->routerMatch = $routerMatch;
    }
    
    /**
     * {@inheritDoc}
     * @see \Interop\Http\ServerMiddleware\MiddlewareInterface::process()
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (null === $this->routerMatch) {
            throw new \InvalidArgumentException('RouterMatch must be set before calling the proces method.');
        }
        return $this->runRouteHandler($request);
    }
    
    /**
     * Resolves the controller/route handler, executes it, and returns the 
     * Response object which it returned.
     * 
     * @param ServerRequestInterface $request
     * @throws \BadMethodCallException In case the route handler did not return
     *      a response object.
     * @return ResponseInterface
     */
    protected function runRouteHandler(ServerRequestInterface $request) : ResponseInterface
    {
        $this->eventDispatcher->dispatch(new EventBeforeAppRouteHandling($this->routerMatch));
        
        $route = $this->routerMatch->getMatchedRoute();
        $routeParams = $this->routerMatch->getMatchedRouteParams();
        if (is_callable($route->getHandler())) {
            $response = $this->container->resolveClosure($route->getHandler());
        } else {
            $handlerClass = $route->getHandler();
            $handlerMethod = $route->getHandlerMethod();
            $instance = $this->container->get($handlerClass);
            $instance->setRequest($request);
            $response = call_user_func_array(array($instance, $handlerMethod), $routeParams);
        }
        if (false === ($response instanceof ResponseInterface)) {
            throw new \BadMethodCallException(
                "Callable handler did not return an instance of \Psr\Http\Message\ResponseInterface"
            );
        }

        $this->eventDispatcher->dispatch(new EventAfterAppRouteHandling($response));
        return $response;
    }
}