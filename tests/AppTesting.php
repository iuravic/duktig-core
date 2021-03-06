<?php
namespace Duktig\Test;

use Duktig\Core\App;
use Duktig\Core\DI\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * This class is intended to be used in test environment instead of the
 * \Duktig\Core\App.
 * 
 * It exposes access to the container making it possible to mock services, etc.
 */
class AppTesting extends App
{
    public function getContainer() : ContainerInterface
    {
        return parent::getContainer();
    }
    
    public function getResponse() : ?ResponseInterface
    {
        return $this->response;
    }
}