<?php
namespace Duktig\Core;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\Factory\ServerRequestFactoryInterface;
use Duktig\Core\DI\ContainerInterface;
use Duktig\Core\Config\ConfigInterface;
use Duktig\Core\Route\Route;
use Duktig\Core\Route\Router\RouterInterface;
use Duktig\Core\Route\Router\RouterMatch;
use Duktig\Core\Exception\Handler\HandlerInterface as ExceptionHandlerInterface;
use Duktig\Core\Middleware\ControllerResponder;
use Duktig\Core\Event\Dispatcher\EventDispatcherInterface;
use Duktig\Core\Http\ResponseSenderInterface;
use Duktig\Core\Event\EventSimple;
use Duktig\Core\Event\CoreEvents\{
    EventBeforeAppHandlingRequest, 
    EventAfterAppHandlingRequest,
    EventBeforeAppMiddlewareFullStackDispatching,
    EventAfterAppMiddlewareFullStackDispatching,
    EventBeforeAppMatchingRoute,
    EventAfterAppMatchingRoute,
    EventBeforeAppReponseSending,
    EventAfterAppReponseSending
};
use Duktig\Core\Exception\{
    HttpException,
    ContainerServiceNotFound
};
use Psr\Http\Message\RequestInterface;

class App
{
    /**
     * @var \Duktig\Core\Config\ConfigInterface $config
     */
    private $config;
    
    /**
     * @var \Psr\Http\Message\ResponseInterface $response
     */
    protected $response;
    
    /**
     * @var \Duktig\Core\DI\ContainerInterface $container
     */
    private $container;
    
    /**
     * @var \Duktig\Core\Route\Router\RouterInterface $router
     */
    private $router;
    
    /**
     * @var \Interop\Http\ServerMiddleware\MiddlewareInterface $middlewareDispatcher
     */
    private $middlewareDispatcher;
    
    /**
     * @var \Duktig\Core\Event\Dispatcher\EventDispatcherInterface $eventDispatcher
     */
    private $eventDispatcher;
    
    /**
     * @var \Duktig\Core\Exception\Handler\HandlerInterface $exceptionHandler
     */
    private $exceptionHandler;
    
    public function __construct(
        ConfigInterface $config,
        ContainerInterface $container,
        RouterInterface $router,
        DelegateInterface $middlewareDispatcher,
        EventDispatcherInterface $eventDispatcher,
        ExceptionHandlerInterface $exceptionHandler,
        ResponseSenderInterface $responseSender
    )
    {
        $this->config = $config;
        $this->container = $container;
        $this->exceptionHandler = $exceptionHandler;
        $this->router = $router;
        $this->middlewareDispatcher = $middlewareDispatcher;
        $this->eventDispatcher = $eventDispatcher;
        $this->responseSender = $responseSender;
        
        $this->eventDispatcher->dispatch(new EventSimple('duktig.core.app.afterConfiguring'));
    }
    
    /**
     * Application entry point. Takes the request as param, or if it is not
     * provided, one is created from the globals, and runs it throught the 
     * whole app stack. The response is then sent to the browser and the app
     * business is finished.
     * 
     * @param ServerRequestInterface $request [optional] If null, creates it from 
     *      the globals
     * @throws Throwable
     */
    public function run(ServerRequestInterface $request = null) : void
    {
        if (null === $request) {
            $request = $this->getContainer()
                ->get(ServerRequestFactoryInterface::class)
                ->createServerRequestFromArray($_SERVER);
        }
        try {
            $this->response = $this->handleRequest($request);
        } catch (\Throwable $e) {
            if ($this->config->getParam('env') != 'prod') {
                throw $e;
            }
            $this->exceptionHandler->report($e);
            $this->response = $this->exceptionHandler->throwableToResponse($e);
        }
        
        $this->sendResponse($this->response);
        $this->terminate();
    }
    
    /**
     * Passes the response through the whole application stack.
     * 
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    private function handleRequest(ServerRequestInterface $request) : ResponseInterface
    {
        $this->eventDispatcher->dispatch(new EventBeforeAppHandlingRequest($request));
        
        $routerMatch = $this->matchRoute($request);
        $stack = $this->getFullMiddlewareStack($request, $routerMatch);
        
        $this->eventDispatcher->dispatch(new EventBeforeAppMiddlewareFullStackDispatching($stack));
        $this->middlewareDispatcher->init($stack);
        $response = $this->middlewareDispatcher->process($request);
        $this->eventDispatcher->dispatch(new EventAfterAppMiddlewareFullStackDispatching($response));
        
        $this->eventDispatcher->dispatch(new EventAfterAppHandlingRequest($response));
        return $response;
    }

    /**
     * Matches the request to a route.
     * 
     * @param ServerRequestInterface $request
     * @throws HttpException
     * @return RouterMatch
     */
    private function matchRoute(ServerRequestInterface $request) : RouterMatch
    {
        $this->eventDispatcher->dispatch(new EventBeforeAppMatchingRoute($request));
        try {
            $routerMatch = $this->router->match($request);
        } catch (\Throwable $e) {
            $statusCode = 404;
            $message = "$statusCode: Router could not match request path {$request->getUri()->getPath()}";
            throw new HttpException($statusCode, $message, null, $e);
        }
        $this->eventDispatcher->dispatch(new EventAfterAppMatchingRoute($request, $routerMatch));
        return $routerMatch;
    }
    
    /**
     * Creates the the complete middleware stack. This stack consists of, from 
     * the entry point to the middle:
     *      - application MW stack (used for every request),
     *      - route MW stack (specific for each route),
     *      - ControllerResponder middleware (which simply returns the response
     *        from the route handler/controller).
     * 
     * @param ServerRequestInterface $request
     * @param RouterMatch $routerMatch
     * @throws NotFoundExceptionInterface If an unresolvable middleware was provided
     * @return array An array of instantiated middleware objects
     */
    private function getFullMiddlewareStack(ServerRequestInterface $request, 
        RouterMatch $routerMatch) : array
    {
        $route = $routerMatch->getMatchedRoute();
        $routeMiddlewares = $this->config->getParam('routes')[$route->getName()]['middlewares'];
        $appMiddlewares = $this->config->getParam('appMiddlewares');
        $stack = [];
        foreach (array_merge($appMiddlewares, $routeMiddlewares) as $class) {
            try {
                $stack[] = $this->getContainer()->get($class);
            } catch (\Throwable $e) {
                throw new ContainerServiceNotFound(
                    "Unable to resolve middleware {$class}", null, $e
                );
            }
        }
        $controllerResponder = $this->getContainer()->get(ControllerResponder::class);
        $controllerResponder->setRouterMatch($routerMatch);
        $stack[] = $controllerResponder;
        return $stack;
    }
    
    protected function getContainer() : ContainerInterface
    {
        return $this->container;
    }
    
    /**
     * Sends the response to the browser.
     * 
     * @param ResponseInterface $response
     * @return void|\Duktig\Core\App
     */
    private function sendResponse(ResponseInterface $response) : App
    {
        $this->eventDispatcher->dispatch(new EventBeforeAppReponseSending($response));
        $this->responseSender->sendResponse($response);
        $this->eventDispatcher->dispatch(new EventAfterAppReponseSending($response));
        return $this;
    }
    
    /**
     * Finishes up the app business. It is the last method to be called after
     * every request.
     */
    private function terminate() : void
    {
        $this->eventDispatcher->dispatch(new EventSimple('duktig.core.app.beforeTerminate'));
    }
}