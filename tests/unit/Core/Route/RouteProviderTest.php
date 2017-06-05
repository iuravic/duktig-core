<?php
namespace Duktig\Core\Route;

use PHPUnit\Framework\TestCase;
use Duktig\Core\Config\ConfigInterface;

class RouteProviderTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        
        $this->configMock = \Mockery::mock(ConfigInterface::class);
        $this->configMock->shouldReceive('getParam')->with('routes')
            ->andReturn($this->getTestRoutesConfigArr());
        $this->routeProvider = new RouteProvider($this->configMock);
    }
    
    public function tearDown()
    {
        parent::tearDown();
        
        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }
        \Mockery::close();
        
        unset($this->routeProvider, $this->configMock);
    }
    
    public function testSetsAndGetsRoutes()
    {
        $this->assertEquals($this->getTestRouteObjectsArr(), 
            $this->routeProvider->getRoutes(),
            "Route provider did not return the expected array of Route objects"
        );
    }

    public function testGetsRoutesFromName()
    {
        $this->assertEquals($this->getTestRouteObjectsArr()[0], 
            $this->routeProvider->getRouteFromName('route1'),
            "Route provider did not return the expcted route by the name 'route1'"
        );
        $this->assertEquals($this->getTestRouteObjectsArr()[1], 
            $this->routeProvider->getRouteFromName('route2'),
            "Route provider did not return the expcted route by the name 'route2'"
        );
        $this->assertNull($this->routeProvider->getRouteFromName('nonexistent-route'),
            "Route provider did not return null for a nonexistant route name"
        );
    }
    
    private function getTestRoutesConfigArr()
    {
        return [
            'route1' => [
                'path' => '/path/to/route1',
                'params_defaults' => [],
                'params_requirements' => [],
                'handler' => 'ControllerOrService',
                'handler_method' => 'handlerMethod',
                'options' => [],
                'host' => '',
                'schemes' => [],
                'methods' => ['GET','POST'],
                'middlewares' => [],
            ],
            'route2' => [
                'path' => '/route-with-invalid-callable-handler{trailingSlash}',
                'params_defaults' => [],
                'params_requirements' => [
                    'trailingSlash' => '/?',
                ],
                'handler' => function (\Interop\Http\Factory\ResponseFactoryInterface $responseFactory) {
                    $response = $responseFactory->createResponse();
                    $response->getBody()->write('Response body set by the callable handler');
                    return $response;
                },
                'handler_method' => '',
                'options' => [],
                'host' => '',
                'schemes' => [],
                'methods' => ['GET'],
                'middlewares' => [],
            ],
        ];
    }
    
    private function getTestRouteObjectsArr()
    {
        $route1 = new Route(
            'route1',
            ['GET','POST'],
            '/path/to/route1',
            [],
            [],
            'ControllerOrService',
            'handlerMethod'
        );
        $route2 = new Route(
            'route2',
            ['GET'],
            '/route-with-invalid-callable-handler{trailingSlash}',
            [],
            ['trailingSlash' => '/?'],
            function (\Interop\Http\Factory\ResponseFactoryInterface $responseFactory) {
                $response = $responseFactory->createResponse();
                $response->getBody()->write('Response body set by the callable handler');
                return $response;
            },
            null
        );
        return [$route1, $route2];
    }
}