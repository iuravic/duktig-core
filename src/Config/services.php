<?php
/**
 * This file registeres the app's core services.
 * 
 * Services are registered by using a closure which receives and returns the 
 * container.
 * 
 * The Auryn container (with an adapted interface) is used as the IoC container.
 * The container must implement \Duktig\Core\DI\ContainerInterface, which is an 
 * extension of the \Psr\Container\ContainerInterface.
 * 
 * If necessary, these service definitions can be skipped all together in your 
 * app by using the @skipCoreServices config param.
 */
return function($container) {
    
    /**
     * Container service, singleton
     */
    $container->alias(
        \Duktig\Core\DI\ContainerInterface::class,
        \Duktig\DI\Adapter\Auryn\AurynAdapter::class
    );
    $container->singleton($container);
    
    /**
     * Config service, singleton
     */
    $container->alias(
        \Duktig\Core\Config\ConfigInterface::class,
        \Duktig\Core\Config\Config::class
    );
    $container->singleton(\Duktig\Core\Config\ConfigInterface::class);
    
    /**
     * Exception handler
     */
    $container->factory(
        \Duktig\Core\Exception\Handler\HandlerInterface::class,
        function(\Duktig\Core\Exception\Handler\Handler $handler) {
            $handler->register();
            return $handler;
        }
    );
    
    /**
     * Response sender
     */
    $container->alias(
        \Duktig\Core\Http\ResponseSenderInterface::class,
        \Duktig\Core\Http\ResponseSender::class
    );
    
    return $container;
};