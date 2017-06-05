<?php
/**
 * Your application's configuration file.
 *      - config values are based on Core/config.php
 *      - all params in this file override those in core config
 */
return [
    // @env - 'dev', 'prod'
    'env' => 'dev',
    // @log - false to write to web server log, or full path to the log file
    'log' => null,
    'appDir' => dirname(__DIR__),
    'view' => [
        // @templateCache - false, or relative path
        'templateCache' => false,
        'templateDirApp' => 'Helpers/Views',
    ],
    'routes' => include 'routes.php',
    'services' => include 'services.php',
    'events' => include 'events.php',
    'appMiddlewares' => include 'middlewares.php',
];