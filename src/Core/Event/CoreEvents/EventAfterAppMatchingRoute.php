<?php
namespace Duktig\Core\Event\CoreEvents;

use Duktig\Core\Event\EventAbstract;
use Psr\Http\Message\RequestInterface;
use Duktig\Core\Route\Router\RouterMatch;

class EventAfterAppMatchingRoute extends EventAbstract
{
    protected $request;
    protected $routerMatch;
    
    public function __construct(RequestInterface $request,
        RouterMatch $routerMatch)
    {
        parent::__construct();
        $this->request = $request;
        $this->routerMatch= $routerMatch;
    }
    
    public function getRequest() : RequestInterface
    {
        return $this->request;
    }
    
    public function getRouterMatch() : RouterMatch
    {
        return $this->routerMatch;
    }
}