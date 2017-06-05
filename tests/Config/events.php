<?php
return [
    'duktig.core.app.beforeTerminate'=> [
        function() {
            echo 'Output echoed by custom listener for event duktig.core.app.beforeTerminate';
        }
    ],
    'duktig.tests.app.testEventRegisteredInConfiguration'=> [
        function() {
            echo 'Simple listener as a closure called on duktig.tests.app.testEventRegisteredInConfiguration';
        }
    ],
    'duktig.tests.app.testEventWhichWillGetAnInvalidListener'=> [
        \Duktig\Test\Event\ListenerWhichDoesNotExist::class
    ],
];