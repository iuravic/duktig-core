<?php
namespace Duktig\Test\Helpers\Event;

use Duktig\Core\Event\EventAbstract;
use Psr\Http\Message\ResponseInterface;

class EventTestLoadingPage extends EventAbstract
{
    private $response;
    
    public function __construct(ResponseInterface$response)
    {
        parent::__construct();
        $this->response= $response;
    }
    
    public function getResponse() : ResponseInterface
    {
        return $this->response;
    }
}