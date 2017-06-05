<?php
namespace Duktig\Core\Event\CoreEvents;

use Duktig\Core\Event\EventAbstract;
use Psr\Http\Message\RequestInterface;

class EventBeforeAppHandlingRequest extends EventAbstract
{
    protected $request;
    
    public function __construct(RequestInterface $request)
    {
        parent::__construct();
        $this->request = $request;
    }
    
    public function getRequest() : RequestInterface
    {
        return $this->request;
    }
}