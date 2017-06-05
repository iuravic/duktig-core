<?php
namespace Duktig\Core\Route;

use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->setRoute();
    }
    
    public function tearDown()
    {
        parent::tearDown();
        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }
        \Mockery::close();
        $this->unsetRoute();
    }
    
    public function testGetsName()
    {
        $this->assertEquals('route1', $this->routeTest->getName(),
            "The test route did not return the expected name");
    }
    
    public function testGetsMethods()
    {
        $this->assertEquals(['GET','POST'], $this->routeTest->getMethods(),
            "The test route did not return the expected methods");
    }
    
    public function testGetsPath()
    {
        $this->assertEquals('/path/to/route1{trailingSlash}',
            $this->routeTest->getPath(),
            "The test route did not return the expected path");
    }
    
    public function testParamsDefaults()
    {
        $this->assertEquals([], $this->routeTest->getParamsDefaults(),
            "The test route did not return the expected default params");
    }
    
    public function testParamsRequirements()
    {
        $this->assertEquals(['trailingSlash' => '/?'],
            $this->routeTest->getParamsRequirements(),
            "The test route did not return the expected params requirements"
        );
    }
    
    public function testGetHandler()
    {
        $this->assertEquals('ControllerOrService', 
            $this->routeTest->getHandler(),
            "The test route did not return the expected handler");
    }
    
    public function testGetHandlerMethod()
    {
        $this->assertEquals('handlerMethod', 
            $this->routeTest->getHandlerMethod(),
            "The test route did not return the expected handler method");
    }
    
    public function testThrowsExceptionIfTryingToSetNullHandlerMethodForWithAHandlerAsAResolvable()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('In case the $handler is not a callable, '
            .'$handlerMethod must specify a method to be called');
        $this->routeTest->setHandler('SomeHandlerID');
    }
    
    private function setRoute()
    {
        $this->routeTest = new Route(
            'route1',
            ['GET','POST'],
            '/path/to/route1{trailingSlash}',
            [],
            ['trailingSlash' => '/?'],
            'ControllerOrService',
            'handlerMethod'
            );
    }
    
    private function unsetRoute()
    {
        unset($this->routeTest);
    }
}