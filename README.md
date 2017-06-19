# duktig-core

This is the core package for the Duktig micro MVC web framework.

# Table of contents
- [About](#about)
    - [duktig-skeleton-web-app package](#duktig-skeleton-web-app-package)
    - [Purpose](#purpose)
    - [Standards](#standards)
    - [Features](#features)
- [Package design](#package-design)
- [Core services](#core-services)
- [Requirements](#requirements)
- [Dependency injection](#dependency-injection)
    - [Container](#container)
    - [Dependency resolution](#dependency-resolution)
    - [Lazy-loading](#lazy-loading)
- [Framework components](#framework-components)
    - [Routing](#routing)
    - [Controllers](#controllers)
    - [Middleware](#middleware)
    - [Templating](#templating)
    - [Events](#events)
    - [Error handling](#error-handling)
- [Configuration](#configuration)
- [Testing](#testing)

<a name="about"></a>
# About

Duktig is a light weight MVC micro web framework written for PHP 7.1. It was primarily created as an educational project, but it is also fully tested and feasible for production use. It implements the MVC pattern and features an IoC container, events system, and uses HTTP middleware.

<a name="duktig-skeleton-web-app-package"></a>
## `duktig-skeleton-web-app` package

The [`duktig-skeleton-web-app`](https://github.com/iuravic/duktig-skeleton-web-app) package is a full featured standalone project which is based on the `duktig-core` package. It can be used as a starting point for developing your own Duktig framework application since it readily implements all the necessary dependencies based on popular open-source projects and packages.

<a name="purpose"></a>
## Purpose

Duktig framework's goal is to deliver a flexible yet powerful framework for creating web applications
by using the most feasible and up-to-date features and practices.

By learning from some of the most popular PHP web frameworks today ([Aura](http://auraphp.com/), [Silex](https://silex.sensiolabs.org/), [Slim](https://www.slimframework.com/), [Stack](http://stackphp.com/), [Yii2](https://github.com/yiisoft/yii2/), [Lumen](https://lumen.laravel.com/), [Symfony](https://symfony.com/), [Laravel](https://laravel.com/), [Bullet](http://bulletphp.com/), [Proton](https://github.com/alexbilbie/Proton)), Duktig's core architecture relies on modern principles and standards.

<a name="standards"></a>
## Standards

- [PSR](http://www.php-fig.org) compliant
  - PSR-1, PSR-2 coding, and PSR-4 autoloading standards
  - interfaces: PSR-3 logger, PSR-7 HTTP message, PSR-11 container, PSR-15 HTTP middlewares, PSR-17 HTTP factories
- decoupled package design
- powered by popular open source projects and libraries
- TDD developed, unit, integration, and functionally tested

<a name="features"></a>
## Features

- PHP 7.1
- MVC pattern
- IoC and DI container
- HTTP middleware
- event system
- lazy loading


<a name="package-design"></a>
# Package design

Most of Duktig's core services are decoupled from the core package and packaged into their own modules. This kind of approach provides high flexibility and a good package design. Using interface injection, the object graph is naturally composed during the execution.


<a name="core-services"></a>
# Core services

The [`duktig-core`](https://github.com/iuravic/duktig-core) implements several of its own core services, while leaving out the implementation of most others to external projects:
- [configuration service](https://github.com/iuravic/duktig-core/blob/master/src/Core/Config/Config.php),
- [exception handler](https://github.com/iuravic/duktig-core/blob/master/src/Core/Exception/Handler/Handler.php),
- [response sender](https://github.com/iuravic/duktig-core/blob/master/src/Core/Http/ResponseSender.php),

These core services are registered in core's [`'Config/services.php'`](https://github.com/iuravic/duktig-core/blob/master/src/Config/services.php) file. If needed they can be overriden and replaced by your own configuration. This is acheived by using the `'skipCoreServices'` config parameter, in which case they must be specified by your own configuration.

`duktig-core` uses the `Auryn DI container` out-of-the-box, which can also be modified by your application.


<a name="requirements"></a>
# Requirements

The `duktig-core` package defines and uses a number of interfaces which need to be implemented by resolvable services at runtime. The [`duktig-skeleton-web-app`](https://github.com/iuravic/duktig-skeleton-web-app) demonstrates how this is done in a real case, and is a recommended starting point for writing your own Duktig framework application. Briefly put, to implement these requirements, an application (for example the `duktig-skeleton-web-app`) first includes all the required packages as [composer dependencies](https://github.com/iuravic/duktig-skeleton-web-app/blob/master/composer.json) and then [registers them as services](https://github.com/iuravic/duktig-skeleton-web-app/blob/master/src/Config/services.php).

Once implemented, the application has access to the implementations of the following interfaces:
- `Interop\Http\Factory\ServerRequestFactoryInterface` and `Interop\Http\Factory\ResponseFactoryInterface` - HTTP message factories
- `Interop\Http\ServerMiddleware\DelegateInterface` - HTTP middleware dispatcher service
- `Duktig\Core\Event\Dispatcher\EventDispatcherInterface` - event dispatcher    
- `Duktig\Core\View\RendererInterface` - template renderer
- `Duktig\Core\Route\Router\RouterInterface` - router service
- `Psr\Log\LoggerInterface` - logger service    



<a name="dependency-injection"></a>
# Dependency injection

<a name="container"></a>
## Container

The DI container must implement the [`Duktig\Core\DI\ContainerInterface`](https://github.com/iuravic/duktig-core/blob/master/src/Core/DI/ContainerInterface.php). This interface is an extension of the `Psr\Container\ContainerInterface` with a several methods of its own.

By default, Duktig out-of-the-box uses the [Auryn container](https://github.com/rdlowrey/auryn), or rather the [`duktig-auryn-adapter`](https://github.com/iuravic/duktig-auryn-adapter) package which adapts to the defined interface. This is defined in the `dutig-core` [configuration](https://github.com/iuravic/duktig-core/tree/master/src/Config). The container can however be changed to any PSR-11 container which additionally implements the `Duktig\Core\DI\ContainerInterface`.

<a name=""></a>
### ContainerFactory

The container itself is instantiated and configured by the [`Duktig\Core\DI\ContainerFactory`](https://github.com/iuravic/duktig-core/blob/master/src/Core/DI/ContainerFactory.php). If the custom container class has any constructor parameters, the `ContainerFactory` will try to resolve and inject them by using the `ReflectionClass`.

The `ContainerFactory` then configures the container, by running it through the service configurators. The services are configured in your app's `services.php` config file.

<a name="dependency-resolution"></a>
## Dependency resolution

As with any standard PHP DI container, the constructor parameter type hinting is used to provide dependency injection. The following entites in the framework are resolved by the container:

- services
- controllers
- closure-type route handlers
- middlewares
- event listeners

These entities will have their constructor parameters resolved and injected at runtime. Any dependency can be injected in this way, either if it was previously defined with the container as a service, or even if it is automatically provisioned, which of course depends on if the container of your choice supports the automatic provisioning feature (the Auryn DI container does).

<a name="lazy-loading"></a>
## Lazy-loading

The framework itself takes advantage of the lazy-loading optimization and delays the object creation in several cases, therefore improving performance. Ie. the controller resolution happens only when the end of the middleware stack is reached, and not sooner. Lazy-loading is naturally related to the container's `make()` method implementation. Therefore, if the container of your choice uses lazy-loading (which the Auryn DI container does), it will also be applied throughout the framework workflow.


<a name="framework-components"></a>
# Framework components

<a name="routing"></a>
## Routing

`duktig-core` defines its routing in terms of several entities. It uses a router which has the job to match the current request to the appropriate route. It also uses a route provider service which provides a simple API for fetching and identifying available routes.

### Router

Duktig's router is featured as a standalone service. The router must implement the [`Duktig\Core\Route\Router\RouterInterface`](https://github.com/iuravic/duktig-core/blob/master/src/Core/Route/Router/RouterInterface.php). This interface defines only one mandatory method `match` which matches the `Psr\Http\Message\ServerRequestInterface` object to a `Duktig\Core\Route\Router\RouterMatch` object. The `RouterMatch` is nothing more than a value object which represents the matched route; it holds the route which was matched and its parameters.

### Route

`Duktig\Core\Route\Route` is the Duktig's route model. Its form was heaviliy influenced by the [Symfony Route's](https://github.com/symfony/routing) route model, as it is one of the most feature-rich and popular route representations in the open-source community.

### RouteProvider

`Duktig\Core\Route\RouteProvider` is a service which provides access to the routes. It accesses the routes' configuration from the configuration services, and converts it to the `Route` objects, exposing them in a way of a several user-friendly API methods.

### Route handlers

A route can have two different kinds of resolvers:

- the first is a classical controller with an action method, where the controller extends the `BaseController` class exposing access to the request and some essential components,
- the second is a closure type handler, which is given directly in route configuration.

Both types of route handlers must return a `ResponseInterface` type object. For a closure type handler, it is recommended to use the `Interop\Http\Factory\ResponseFactoryInterface` to create a response instance, while a controller will allready have the response prepared for use via the `BaseController` parent class.

Both types of handlers are dynamically resolved by the container, and have their dependencies injected upon creation.


<a name="controllers"></a>
## Controllers

Controllers are assigned to routes and are in charge of generating a response. Alternatively, instead of defining special controller classes, a simpler [closure-type route handlers](TODO) can be used.

### `BaseController`
All controller classes should extend the base `Duktig\Core\Controller\BaseController` in order to get access to the application context, including the properties:
- `$request` - the PSR-7 request object
- `$response` - - a "fresh" PSR-7 response object
- `$queryParams` - parsed URI parameters
- `$renderer` - template rendering service
- `$config` - configuration service

`BaseController` also provides methods for quicker manipulation of the response object and its rendering.

### Route parameters

Route parameters are passed to the controller as the action method's parameters. Ie. if a route uses one string parameter `$param`, and assigns it to the `exampleAction` method, the parameter will be available to the action method in this way:

```php
public function exampleAction(string $param) : ResponseInterface;
```

### Return type

Every controller or route handler must return a PSR-7 response object. The `$response` property is available for use within controller that extend the main `BaseController` class, which is internally generated by a PSR-17 `$responseFactory` service.

### Dependency injection

Controllers will have their constructor parameters resolved and injected at runtime. Controllers, among other entites, are by default not given the access to the container, as this is widely considered as the `service locator antipattern`. However, no special restriction is imposed on this approach neither, and could easily be implemented. This practice is, however, strongly discouraged.

It may also be needless to point out, but, naturally, when your controller defines it's own dependencies, it must also pay respect to its parent's dependencies as well, ie.:

```php
<?php
namespace MyProject\Controller;

use Duktig\Core\Controller\BaseController;
use Interop\Http\Factory\ResponseFactoryInterface;
use Duktig\Core\View\RendererInterface;
use Duktig\Core\Config\ConfigInterface;
use MyProject\Service\CustomService;

class IndexController extends BaseController
{
    private $customService;
    
    public function __construct(
        CustomService $customService,
        ResponseFactoryInterface $responseFactory,
        RendererInterface $renderer,
        ConfigInterface $config
    )
    {
        parent::__construct($responseFactory, $renderer, $config);
        $this->customService = $customService;
    }
}
``` 

### Lazy loading

The controller is resolved and instantiated only at the point when it is reached by the command chain. The special `ControllerResponder` middleware is used to resolve and execute the controller, and return its response to the application.


<a name="middleware"></a>
## Middleware

Duktig uses the "single-pass" HTTP middleware which corresponds to the [PSR-15](http://www.php-fig.org/psr/) specification. It implies the implementation of the `Psr\Http\ServerMiddleware\MiddlewareInterface` and the method with the following signature:

```php
public function process(ServerRequestInterface $request, DelegateInterface $delegate);
```

Likewise the middleware dispatching system must implement the `Psr\Http\ServerMiddleware\DelegateInterface` with the following method:

```php
public function process(ServerRequestInterface $request);
```

Duktig leaves out the implementation of the middleware dispatching system from its core functionality, and delegates it to an external package. The [`duktig-skeleton-web-app`](https://github.com/iuravic/duktig-skeleton-web-app) uses the `mindplay\middleman` middleware dispatcher, or rather its adapter [`iuravic/duktig-middleman-adapter`](https://github.com/iuravic/duktig-middleman-adapter) which wraps the Middleman's API slightly.

### Application and route middleware

Two kinds of middlewares are used in Duktig:

- application middleware - global for the whole application, it is run on each request,
- route middleware - variable, can be assigned to a specific route.

### ControllerResponder

The ControllerResponder is a special middleware which lies at the end of the middleware stack. It resolves the route handler, calls it, and returns its response back to the middleware stack. Since it is used as a "responder" from the route handler's perspective, hence its name.

### The middleware stack

The full middleware stack which the request traverses consists of:
- application middleware
- route middleware
- the ControllerResponder middleware

### Dependency injection

All middlewares are instantiated by the container, therefore will have their constructor dependencies injected.


<a name="templating"></a>
## Templating

The template rendering service is defined by the `Duktig\Core\View\RendererInterface`. It provides a simple API necessary to use the templating.


<a name="events"></a>
## Events

### Event dispatcher

The event dispatcher is defined by the `Duktig\Core\Event\Dispatcher\EventDispatcherInterface`. It implies that a container is provided to the dispatcher, which is then used to resolve the listeners. Therefore the event listeners will have their dependencies injected and be lazy-loaded when their events are dispatched.

### Event

Events in Duktig are simply value objects which contain the contextual information for the listeners to act upon. It is also correct to say that an event is just a value object with a unique name.

Two different event types can be used in Duktig.

#### Event as its separate class

An event can be created as its own class, which must extend the `Duktig\Core\Event\EventAbstract` class.

In this case, its name can but does not have to be specifically provided (as the constructor parameter), and a default event's name will be its fully qualified class name without the prefix backslash. Ie. for an event class `MyProject\Event\CustomEvent`, its default name will be `'MyProject\Event\CustomEvent'`.

Here is a simple example of firing an event which is defined in its own separate class. Let us assume the `UserEvent` takes the parameter `$user` as the constructor parameter. Dispatching this event is as simple as:

```php
$event = new \DemoApp\Event\UserEvent($user);
$eventDispatcher->dispatch($event);
```

#### Simple event

In the case of a simplest event which is represented only by its unique name, and does not need to hold any other information for the listener's handler to use, instead of writing a separate class for the event, the existing `Duktig\Core\Event\EventSimple` class can be used to instantiate an event on-the-fly.

In this case, a unique event name must be given to the constructor. Since the `EventSimple` can be used to instantiate different events, each of those events is held responsible for their own unique naming.

The simple event can be dispatched by instantiating the `EventSimple` object on the fly, ie:

```php
$eventDispatcher->dispatch(new \Duktig\Core\Event\EventSimple('theEventName'));  
```

### Listeners

The event listener may either be provided as resolvable class/service name or as a simple closure.

In the first case, in which the listener is a separate class, it must implement the `Duktig\Core\Event\ListenerInterface`. When the event is dispatched, the listener is be resolved by the container and have all its constructor dependencies injected.

In case the listener is given as a simple closure, it is not resolved by the container, so no dependencies will be injected. The closure-type listener expect only one optional argument, the event:

```php
function($event) { /* ... */ }
```

### Core events

The framework dispatches its core events throughout the points of interest in the application flow. The full list of Duktig's core events is found in the `duktig-core`'s [`events.php`](https://github.com/iuravic/duktig-core/blob/master/src/Config/events.php) file. Some core events are only defined by their unique names (ie. `'duktig.core.app.afterConfiguring'`), while others are created as separate classes.


<a name="error-handling"></a>
## Error handling

Duktig uses its own error and exception handler which implements the `Duktig\Core\Exception\Handler\HandlerInterface`. Its basic tasks are to register the error handling throughout the application, to convert a `\Throwable` into a response, and to report the occurence of such an error.

It takes in cosideration the PHP 7 error and exception handling mechanisms. From the PHP 7 version, both the `\Error` and the `\Exception` classes implement the `\Throwable` interface. Instead of halting script execution, some fatal errors and recoverable errors now throw exceptions. Also, an uncaught exception will continue to produce a fatal error, and in this same way an `\Error` exception thrown from an uncaught fatal error still produces a fatal error.

In production environment, exceptions and errors are rendered through default or custom error templates. Handler prioritizes the templates by their locations and names. It searches for the most specific template it can find for the given throwable, while first trying to locate the template in the application custom template path, and if none are found it uses the default templates from the `duktig-core` package. It searches and renders an error template in following steps:
- if an `HttpException` is thrown, it searches for the template with the error code for its name,
- if no such template is found, as well as for all other exception types, it looks for a template 
 with name equal to the exception class name,
- finally, it searches for a generic error template.

The renderer service is itself given the location of the templates, both for the custom templates within the application dir, and the default templates whithin the framework core dir. In this way it first looks for custom, and then for default templates.



<a name="configuration"></a>
# Configuration

The configuration specifics are described within the [`duktig-skeleton-web-app` project](https://github.com/iuravic/duktig-skeleton-web-app) where it is seen in action. The skeleton application takes the `duktig-core` and provides it with all its dependencies, employing it into the full Duktig environment.



<a name="testing"></a>
# Testing

The `duktig-core` and all the other packages implemented by the `duktig-skeleton-web-app` are fully tested using PHPUnit and Mockery.

A special [`Duktig\Test\AppTesting`](https://github.com/iuravic/duktig-core/blob/master/tests/AppTesting.php) class is available for the testing environment. It extends access to the container and to the response object. It can be used to easily mock services, and to gain direct access to the response.
