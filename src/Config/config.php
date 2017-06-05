<?php
/**
 * Application core configuration file. 
 * 
 * All params in this file are overridden by your app's custom config file, 
 * except the @services definitions which are merged together by default.
 * 
 * If the core @services definitons need to be skipped (ie. in case a different
 * IoC container is implemented), the @skipCoreServices param needs to be set 
 * to true.
 */
return [
    // @env - 'dev', 'prod'
    'env' => 'prod',
    // @log - full path to the log file, or false to write to web server log
    'log' => false,
    
    // @coreDir - app param, do not change
    'coreDir' => dirname(__DIR__),
    
    // @view - all paths in @view are relative to app dir
    'view' => [
        // @templateDirApp - your app's template sub dir
        'templateDirApp' => 'Views',
        // @templateErrorSubDirApp - your app's error template sub dir
        'templateErrorSubDirApp' => 'Errors',
        // @templateSuffix - template file suffix
        'templateSuffix' => '.html.twig',
        // @templateCache - (relative path to) template cache dir, or false not to use
        'templateCache' => 'var/cache/twig',
        // @templateDirCore - core app's template sub dir
        'templateDirCore' => 'Views',
        // @templateErrorSubDirCore - core app's error templates sub dir
        'templateErrorSubDirCore' => 'Errors',
        // @templateErrorGeneric - generic error template name
        'templateErrorGeneric' => 'Error',
    ],
    
    // @serviceContainer - class must implement \Duktig\Core\DI\ContainerInterface
    'serviceContainer' => \Duktig\DI\Adapter\Auryn\AurynAdapter::class,
    // @skipCoreServices bool - should core services definitions be skipped
    'skipCoreServices' => false,
    // @services - core services definitions
    'services' => include 'services.php',

    // @params - your app's params
    'params' => include 'params.php',
    // @routes - your app's routes definitions
    'routes' => include 'routes.php',
    // @appMiddlewares - application wide mdw stack
    'appMiddlewares' => include 'middlewares.php',
    // @events - core events
    'events' => include 'events.php',
];