<?php
namespace Duktig\Core;

use PHPUnit\Framework\TestCase;
use Duktig\Test\AppTesting;
use Psr\Http\Message\ResponseInterface;
use Duktig\Http\Factory\Adapter\Guzzle\GuzzleServerRequestFactory;
use Duktig\Core\Exception\HttpException;

class AppTest extends TestCase
{
    protected $app;
    
    public function setUp()
    {
        parent::setUp();
        $this->app = (new AppFactory())->make(
            __DIR__.'/../../Config/configTest.php',
            AppTesting::class
        );
    }
    
    public function tearDown()
    {
        parent::tearDown();
        unset($this->app);
    }
    
    public function testApplicationCreated()
    {
        $this->assertInstanceOf(App::class, $this->app,
            "AppFactory did not resolve an object of the expected type");
    }
    
    public function testConvertsRequestIntoResponse()
    {
        $request = $this->getRequest('/route-with-callable-handler');
        ob_start();
        $this->app->run($request);
        ob_end_clean();
        $response = $this->app->getResponse();
        $this->assertInstanceOf(ResponseInterface::class, $response,
            "getResponse() should have returned ResponseInterface object but didn't");
    }
    
    public function testGetsResponseFromRouteWithACallableHandler()
    {
        $request = $this->getRequest('/route-with-callable-handler');
        $this->app->run($request);
        $this->expectOutputRegex('/Response body set by the callable handler/');
    }
    
    public function testGetsResponseFromRouteWithAResolvableController()
    {
        $request = $this->getRequest('/resolvable/page/testUP1/testUP2?conf=qval1&testqp2=qval2');
        $this->app->run($request);
        $this->expectOutputRegex('/Body set by ResolvableController::pageAction/');
    }
    
    public function testUriAndQueryParamsArePassedToController()
    {
        $request = $this->getRequest('/resolvable/page/testUP1/testUP2?conf=qval1&testqp2=qval2');
        $this->app->run($request);
        $string = 'Uri params: '.json_encode(['uriParam1' => 'testUP1','uriParam2' => 'testUP2'])
            ."\n".'Query params: '.json_encode(['conf' => 'qval1','testqp2' => 'qval2']);
        $this->expectOutputRegex('/'.$string.'/');
    }
    
    public function testResponseIsRenderedInTemplate()
    {
        $request = $this->getRequest('/template-test');
        $this->app->run($request);
        $this->expectOutputRegex('/<h1>Test Template<\/h1>/');
        $this->expectOutputRegex('/<p>Lets render some text: testing123<\/p>/');
    }
    
    public function testThrowsExceptionIfNoTemplateIsFound()
    {
        $request = $this->getRequest('/missing-template-test');
        $this->expectException(\Duktig\Core\Exception\TemplateNotFoundException::class);
        $this->app->run($request);
    }
    
    public function testThrowsExceptionIfAnUnresolvableRouteHandlerWasProvided()
    {
        $request = $this->getRequest('/route-with-invalid-callable-handler');
        $this->expectException(\Psr\Container\ContainerExceptionInterface::class);
        $this->expectExceptionMessage("Unable to get: injectedService");
        $this->app->run($request);
    }
    
    public function testThrowsExceptionIfRouteHandlerDoesNotReturnMandatoryResponseObject()
    {
        $request = $this->getRequest('/route-with-handler-with-invalid-return-type');
        $this->expectException(\BadMethodCallException::class);
        $this->app->run($request);
    }
    
    public function testThrowsExceptionIfNoRouteIsMatched()
    {
        $request = $this->getRequest('/path-doesnt-exist');
        $this->expectException(HttpException::class);
        $this->app->run($request);
    }
    
    public function testServiceWithDependencyIsResolved()
    {
        $request = $this->getRequest('/dependency-test');
        $this->app->run($request);
        $this->expectOutputRegex('/Logging service provided instanceof Monolog\\\Logger/');
    }
    
    public function testCustomServiceWithDependencyIsResolved()
    {
        $request = $this->getRequest('/custom-service-resolution-test');
        $this->app->run($request);
        $this->expectOutputRegex(
            '/Custom service lazy loaded and injected with its own dependencies'
            .' Duktig\\\Test\\\Helpers\\\Service\\\TestService/'
        );
    }
    
    public function testThrowsExceptionIfAppCannotResolveAService()
    {
        $request = $this->getRequest('/invalid-dependency-test');
        $this->expectException(\Psr\Container\ContainerExceptionInterface::class);
        $this->expectExceptionMessage(
            "Unable to get: Duktig\Test\Helpers\Controller\InvalidDependencyController"
        );
        $this->app->run($request);
    }
    
    public function testRunsApplicationGlobalMiddleware()
    {
        $request = $this->getRequest('/resolvable/page/testUP1/testUP2?conf=qval1&testqp2=qval2');
        $this->app->run($request);
        $this->expectOutputRegex('/<!-- Response modified by TestAppMiddleware -->/');
    }
    
    public function testRunsRouteSpecificMiddleware()
    {
        $request = $this->getRequest('/route-specific-middleware-test');
        $this->app->run($request);
        $this->expectOutputRegex('/Response body modified by TestRouteSpecificMiddleware/');
    }
    
    public function testThrowsExceptionIfAppCannotResolveRouteMiddleware()
    {
        $request = $this->getRequest('/route-invalid-middleware-test');
        $this->expectException(\Psr\Container\NotFoundExceptionInterface::class);
        $this->expectExceptionMessage(
            "Unable to resolve middleware Duktig\Test\Helpers\Middleware\NonexistantMiddleware"
        );
        $this->app->run($request);
    }
    
    public function testACoreEventOnAppTerminateIsDispatchedAndTheListenerIsExecuted()
    {
        $request = $this->getRequest('/resolvable/page/testUP1/testUP2?conf=qval1&testqp2=qval2');
        $this->app->run($request);
        $this->expectOutputRegex('/Output echoed by custom listener for event duktig.core.app.beforeTerminate/');
    }
    
    public function testEventIsRegisteredProgrammaticallyAndTheListenerIsExecuted()
    {
        $request = $this->getRequest('/custom-event-test');
        $this->app->run($request);
        $this->expectOutputRegex('/EventTestListener has altered the response when EventTest was triggered/');
    }
    
    public function testEventIsRegisteredInConfigAndListenerAsCallableIsExecuted()
    {
        $request = $this->getRequest('/custom-event-configuration-test');
        $this->app->run($request);
        $this->expectOutputRegex(
            '/Simple listener as a closure called on duktig.tests.app.testEventRegisteredInConfiguration/'
        );
    }
    
    public function testThrowsExceptionIfListenerCannotBeResolved()
    {
        $request = $this->getRequest('/event-with-invalid-listener');
        $this->expectException(\Psr\Container\NotFoundExceptionInterface::class);
        $this->expectExceptionMessage(
            "Invalid service as listener 'Duktig\Test\Event\ListenerWhichDoesNotExist'"
            . " provided for event 'duktig.tests.app.testEventWhichWillGetAnInvalidListener',"
            . " and it cannot be resolved"
        );
        $this->app->run($request);
    }
    
    protected function getRequest(string $uri, string $method = 'GET')
    {
        return (new GuzzleServerRequestFactory)->createServerRequestFromArray(
            ['REQUEST_METHOD' => $method, 'REQUEST_URI' => $uri,]
        );
    }
}