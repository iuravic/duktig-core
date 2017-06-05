<?php
namespace Duktig\Core\Route\Router;

use PHPUnit\Framework\TestCase;
use Duktig\Core\Route\Route;

class RouterMatchTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->setRouterMatch();
    }
    
    public function tearDown()
    {
        parent::tearDown();
        $this->unsetRouterMatch();
    }
    
    private function setRouterMatch()
    {
        $this->routeMock = \Mockery::mock(Route::class);
        $this->paramsArr = ['p1' => 'val1', 'p2' => 'val2'];
        $this->routerMatch = new RouterMatch($this->routeMock, $this->paramsArr);
    }
    
    private function unsetRouterMatch()
    {
        unset($this->routeMock, $this->paramsArr, $this->routerMatch);
    }
    
    public function testGetsRoute()
    {
        $this->assertEquals($this->routeMock, $this->routerMatch->getMatchedRoute(),
            "getMatchedRoute() did not return the expected RouteMock obj");
    }
    
    public function testRouteParams()
    {
        $this->assertEquals($this->paramsArr, $this->routerMatch->getMatchedRouteParams(),
            "getMatchedRouteParams() did not return the expected array");
    }
}