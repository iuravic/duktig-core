<?php
namespace Duktig\Core\Exception\Handler;

use Psr\Http\Message\ResponseInterface;

interface HandlerInterface
{
    /**
     * Configure and register the error reporting and handling.
     */
    public function register() : void;
    
    /**
     * Report (ie. log) the throwable.
     * 
     * @param \Throwable $e
     */
    public function report(\Throwable $e) : void;
    
    /**
     * Convert a throwable to a response object.
     * 
     * @param \Throwable $e
     * @return Psr\Http\Message\ResponseInterface
     */
    public function throwableToResponse(\Throwable $e) : ResponseInterface;
}