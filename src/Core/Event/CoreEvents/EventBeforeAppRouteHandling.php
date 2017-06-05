<?php
namespace Duktig\Core\Event\CoreEvents;

use Duktig\Core\Event\EventAbstract;
use Duktig\Core\Route\Router\RouterMatch;

class EventBeforeAppRouteHandling extends EventAbstract
{
    protected $routerMatch;
    
    public function __construct(RouterMatch $routerMatch)
    {
        parent::__construct();
        $this->routerMatch = $routerMatch;
    }
    
    public function getRouterMatch() : RouterMatch
    {
        return $this->routerMatch;
    }
}