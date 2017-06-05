<?php
namespace Duktig\Core\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Duktig\Core\Event\Dispatcher\EventDispatcherInterface;

class ResponseSenderTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }
    
    public function tearDown()
    {
        parent::tearDown();
        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }
        \Mockery::close();
    }
    
    public function testSendsBodyAndDispatchesEvent()
    {
        $mockResponse = \Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getBody->__toString')->andReturn('html body');
        $mockDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $mockDispatcher->shouldReceive('dispatch')->once();
        (new ResponseSender($mockDispatcher))->sendResponse($mockResponse);
        $this->expectOutputRegex('/html body/');
    }
    
    public function testSendsStatusLine()
    {
        $mockResponse = \Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getProtocolVersion')->andReturn('1.1');
        $mockResponse->shouldReceive('getStatusCode')->andReturn(404);
        $mockResponse->shouldReceive('getReasonPhrase')->andReturn('Not found');
        
        $mockResponseSenderPartial = \Mockery::mock(ResponseSender::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $mockResponseSenderPartial->shouldReceive('phpHeader')
            ->withArgs(['HTTP/1.1 404 Not found', true, 404]);
        
        $mockResponseSenderPartial->sendStatusLine($mockResponse);
    }
    
    public function testSendHeaders()
    {
        $mockResponse = \Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getStatusCode')->andReturn(200);
        $mockResponse->shouldReceive('getHeaders')
            ->andReturn(['Cache-Control' => ['no-cache', 'no-store']]);
        
        $mockResponseSenderPartial = \Mockery::mock(ResponseSender::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $mockResponseSenderPartial->shouldReceive('phpHeader')
            ->withArgs(['Cache-Control: no-cache', false, 200]);
        $mockResponseSenderPartial->shouldReceive('phpHeader')
            ->withArgs(['Cache-Control: no-store', false, 200]);
        
        $mockResponseSenderPartial->sendHeaders($mockResponse);
    }
}