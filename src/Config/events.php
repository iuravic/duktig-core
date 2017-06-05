<?php
/**
 * This is a list of all core events, with no listeners currently attached to them.
 */
return [
    'duktig.core.app.afterConfiguring' => [],
    \Duktig\Core\Event\CoreEvents\EventBeforeAppHandlingRequest::class => [],
    \Duktig\Core\Event\CoreEvents\EventAfterAppHandlingRequest::class=> [],
    \Duktig\Core\Event\CoreEvents\EventBeforeAppMiddlewareFullStackDispatching::class=> [],
    \Duktig\Core\Event\CoreEvents\EventAfterAppMiddlewareFullStackDispatching::class=> [],
    \Duktig\Core\Event\CoreEvents\EventBeforeAppMatchingRoute::class=> [],
    \Duktig\Core\Event\CoreEvents\EventAfterAppMatchingRoute::class=> [],
    \Duktig\Core\Event\CoreEvents\EventBeforeAppRouteHandling::class=> [],
    \Duktig\Core\Event\CoreEvents\EventAfterAppRouteHandling::class=> [],
    \Duktig\Core\Event\CoreEvents\EventBeforeAppReponseSending::class=> [],
    \Duktig\Core\Event\CoreEvents\EventAfterAppReponseHeadersSending::class => [],
    \Duktig\Core\Event\CoreEvents\EventAfterAppReponseSending::class => [],
    'duktig.core.app.beforeTerminate'=> [],
];