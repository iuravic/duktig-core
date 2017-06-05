<?php
namespace Duktig\Core\Exception\Handler;

use PHPUnit\Framework\TestCase;
use Duktig\Core\Config\ConfigInterface;
use Psr\Log\LoggerInterface;
use Interop\Http\Factory\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Duktig\Core\View\RendererInterface;
use Duktig\Core\Http\ResponseSenderInterface;

class HandlerTest extends TestCase
{
    private $mockResponseFactory;
    private $mockResponse;
    private $mockConfig;
    private $mockRenderer;
    private $mockLogger;
    private $mockResponseSender;
    
    public function setUp()
    {
        parent::setUp();
        $this->setMockDependencies();
    }
    
    public function tearDown()
    {
        parent::tearDown();
        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }
        \Mockery::close();
        $this->unsetMockDependencies();
    }
    
    public function testRegisterCorrectErrorHandlingAndReportingForProdEnv()
    {
        $this->mockResponseFactory->shouldReceive('createResponse');
        $this->mockConfig->shouldReceive('getParam')->with('env')
            ->andReturn('prod');
        
        $handler = $this->getHandler();
        $handler->register();
        
        $this->assertEquals('Off', ini_get('display_errors'),
            "display_erors wasn't set to 'Off' in 'prod' env");
        $this->assertEquals(E_ALL, error_reporting(),
            "error_reporting wasn't set to E_ALL in 'prod' env");
    }
    
    public function testRegisterCorrectErrorHandlingAndReportingForNonProdEnv()
    {
        $this->mockResponseFactory->shouldReceive('createResponse');
        $this->mockConfig->shouldReceive('getParam')->with('env')
            ->andReturn('dev');
        
        $handler = $this->getHandler();
        $handler->register();
        
        $this->assertEquals('On', ini_get('display_errors'),
            "display_errors wasn't set to 'On' in 'dev' env");
        $this->assertEquals(E_ALL, error_reporting(),
            "error_reporting wasn't set to E_ALL in 'dev' env");
    }
    
    public function testHandleExceptionRethrowsExceptionForNonProdEnv()
    {
        $exceptionMock = \Mockery::mock(\Exception::class);
        
        $this->mockResponseFactory->shouldReceive('createResponse');
        $this->mockConfig->shouldReceive('getParam')->with('env')->once()
            ->andReturn('dev');
        $this->mockLogger->shouldReceive('error')->once()
            ->with($exceptionMock);
        $this->expectException(\Exception::class);
        
        $handler = $this->getHandler();
        $handler->handleException($exceptionMock);
    }
    
    public function testErrorHandlerRethrowsExceptionIfErrorReportingIsTurnedOn()
    {
        $this->expectException(\ErrorException::class);
        $this->mockResponseFactory->shouldReceive('createResponse');
        
        $handler = $this->getHandler();
        // error_reporting is set for the runtime only
        error_reporting(E_ALL);
        $handler->handleError(0, "msg");
    }
    
    public function testHandleExceptionMethodRendersTemplateWithStatusCodeForItsName()
    {
        $statusCode = 404;
        
        $templateNameByStatusCode = 'ErrorTemplates/404.html.twig';
        $templateContent = 'content of template '.$templateNameByStatusCode;
        
        $exceptionMock = \Mockery::mock(\Duktig\Core\Exception\HttpException::class, [$statusCode]);
        $exceptionMock->shouldReceive('getStatusCode')->andReturn($statusCode);
        
        $this->setConfigMockProd();
        
        $this->mockResponseFactory->shouldReceive('createResponse')->once()
            ->andReturn($this->mockResponse);

        $this->mockResponse->shouldReceive('withStatus')->once()->with($statusCode)->andReturnSelf();
        $this->mockResponse->shouldReceive('getStatusCode')->once()->andReturn($statusCode);
        $this->mockResponse->shouldReceive('getBody->__toString')->once()->andReturn($templateContent);
        $this->mockResponse->shouldReceive('getBody->write')->once()->with($templateContent);
                    
        $this->mockRenderer->shouldReceive('render')->once()->with($templateNameByStatusCode)
            ->andReturn($templateContent);
        
        $this->mockLogger->shouldReceive('error')->once()->with($exceptionMock);
        
        $this->mockResponseSender->shouldReceive('sendResponse')->once()
            ->with(\Mockery::on(function ($response) use ($statusCode, 
                $templateContent
                ) {
                    if ($statusCode == $response->getStatusCode()
                        && $templateContent == $response->getBody()->__toString()
                    ) {
                        return true;
                    }
                    return false;
                }
            ));
        
        $handler = $this->getHandler();
        $handler->handleException($exceptionMock);
    }
    
    public function testHandleExceptionMethodRendersTemplateWithExceptionClassForItsNameAndSetsStatusCodeFrom0To500()
    {
        $statusCode = 0;
        $expectedStatusCode = 500;
        $exception = new \InvalidArgumentException('', $statusCode);
        $templateNameByStatusCode = 'ErrorTemplates/InvalidArgumentException.html.twig';
        $templateContent = 'content of template '.$templateNameByStatusCode;

        $this->setConfigMockProd();
        
        $this->mockResponse->shouldReceive('getStatusCode')->once()->andReturn($statusCode);
        $this->mockResponse->shouldReceive('withStatus')->once()->with($expectedStatusCode)->andReturnSelf();
        $this->mockResponse->shouldReceive('getStatusCode')->once()->andReturn($expectedStatusCode);
        $this->mockResponse->shouldReceive('getBody->write')->once()->with($templateContent);
        $this->mockResponse->shouldReceive('getBody->__toString')->once()->andReturn($templateContent);

        $this->mockResponseFactory->shouldReceive('createResponse')->once()
            ->andReturn($this->mockResponse);
        
        $this->mockRenderer->shouldReceive('render')->once()
            ->with($templateNameByStatusCode)
            ->andReturn($templateContent);
        
        $this->mockLogger->shouldReceive('error')->once()->with($exception);
        
        $this->mockResponseSender->shouldReceive('sendResponse')->once()
            ->with(\Mockery::on(function ($response) use ($expectedStatusCode, 
                $templateContent
                ) {
                    if ($expectedStatusCode == $response->getStatusCode()
                        && $templateContent == $response->getBody()->__toString()
                    ) {
                        return true;
                    }
                    return false;
                }
            ));
        
        $handler = $this->getHandler();
        $handler->handleException($exception);
    }
    
    public function testHandleExceptionMethodRendersDefaultTemplate()
    {
        $statusCode = 20;
        $exception = new \InvalidArgumentException('', $statusCode);
        $templateDefault = 'ErrorTemplates/ErrorDefault.html.twig';
        $templateContent = 'content of template '.$templateDefault;

        $this->setConfigMockProd();
        
        $this->mockResponse->shouldReceive('withStatus')->once()->with($statusCode)->andReturnSelf();
        $this->mockResponse->shouldReceive('getStatusCode')->once()->andReturn($statusCode);
        $this->mockResponse->shouldReceive('getBody->write')->once()->with($templateContent);
        $this->mockResponse->shouldReceive('getBody->__toString')->once()->andReturn($templateContent);

        $this->mockResponseFactory->shouldReceive('createResponse')->once()
            ->andReturn($this->mockResponse);
        
        $this->mockRenderer->shouldReceive('render')
            ->andReturnUsing(function($argument) use ($templateDefault, $templateContent) {
                    if ($argument == $templateDefault) {
                        return $templateContent;
                    }
                    return false;
                }
            );
        $this->mockLogger->shouldReceive('error')->once()->with($exception);
        $this->mockResponseSender->shouldReceive('sendResponse')->once()
            ->with(\Mockery::on(function ($response) use ($statusCode,
                $templateContent
                ) {
                    if ($statusCode == $response->getStatusCode()
                        && $templateContent == $response->getBody()->__toString()
                    ) {
                        return true;
                    }
                    return false;
                }
            ));
        
        $handler = $this->getHandler();
        $handler->handleException($exception);
    }
    
    public function testHandleExceptionMethodFindsNoTemplateAndRendersDefaultExceptionHTML()
    {
        $statusCode = 20;
        $exception = new \InvalidArgumentException('', $statusCode);
        
        $mockException = \Mockery::mock(\Exception::class);
        $mockHandlerProxy = \Mockery::mock(Handler::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $expectedHTML = $mockHandlerProxy->getHtmlForException($mockException);
        
        $this->setConfigMockProd();
        
        $this->mockResponse->shouldReceive('withStatus')->once()->with($statusCode)->andReturnSelf();
        $this->mockResponse->shouldReceive('getBody->write')->once()->with($expectedHTML);

        $this->mockResponseFactory->shouldReceive('createResponse')->once()
            ->andReturn($this->mockResponse);
        
        $this->mockRenderer->shouldReceive('render')
            ->andReturn(false);
        $this->mockLogger->shouldReceive('error')->once()->with($exception);
        $this->mockResponseSender->shouldReceive('sendResponse')->once();
            
        $handler = $this->getHandler();
        $handler->handleException($exception);
    }
    
    private function setMockDependencies()
    {
        $this->mockResponseFactory = \Mockery::mock(ResponseFactoryInterface::class);
        $this->mockResponse = \Mockery::mock(ResponseInterface::class);
        $this->mockConfig = \Mockery::mock(ConfigInterface::class);
        $this->mockRenderer = \Mockery::mock(RendererInterface::class);
        $this->mockLogger = \Mockery::mock(LoggerInterface::class);
        $this->mockResponseSender = \Mockery::mock(ResponseSenderInterface::class);
    }
    
    private function unsetMockDependencies()
    {
        unset(
            $this->mockResponseFactory,
            $this->mockConfig,
            $this->mockRenderer,
            $this->mockLogger,
            $this->mockResponseSender
        );
    }
    
    private function setConfigMockProd()
    {
        $this->mockConfig->shouldReceive('getParam')->with('env')->andReturn('prod');
        $this->mockConfig->shouldReceive('getParam')->with('view')->times(3)
            ->andReturn([
                'templateSuffix' => '.html.twig',
                'templateErrorSubDirApp' => 'ErrorTemplates',
                'templateErrorGeneric' => 'ErrorDefault'
            ]);
    }
    
    private function getHandler($responseFactory = null, $config = null,
        $renderer = null, $logger = null, $responseSender = null)
    {
        return new Handler(
            $responseFactory ?? $this->mockResponseFactory,
            $config ?? $this->mockConfig,
            $renderer ?? $this->mockRenderer,
            $logger ?? $this->mockLogger,
            $responseSender ?? $this->mockResponseSender
        );
    }
}