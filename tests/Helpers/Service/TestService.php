<?php
namespace Duktig\Test\Helpers\Service;

use Psr\Log\LoggerInterface;

/**
 * In the simplest way possible, this service shows using the injection
 * of dependencies. Logger service is here resolved and passed to the service.
 */
class TestService
{
    public function __construct(LoggerInterface $logger)
    {
    }
}