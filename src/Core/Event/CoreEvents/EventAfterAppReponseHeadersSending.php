<?php
namespace Duktig\Core\Event\CoreEvents;

use Duktig\Core\Event\EventAbstract;
use Psr\Http\Message\ResponseInterface;

class EventAfterAppReponseHeadersSending extends EventAbstract
{
    protected $response;
    
    public function __construct(ResponseInterface $response)
    {
        parent::__construct();
        $this->response = $response;
    }
    
    public function getResponse() : ResponseInterface
    {
        return $this->response;
    }
}