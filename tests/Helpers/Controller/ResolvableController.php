<?php
namespace Duktig\Test\Helpers\Controller;

use Duktig\Core\Controller\BaseController;
use Psr\Http\Message\ResponseInterface;
use Interop\Http\Factory\ResponseFactoryInterface;
use Duktig\Core\View\RendererInterface;
use Duktig\Core\Config\ConfigInterface;
use Duktig\Test\Helpers\Service\TestService;
use Duktig\Core\Event\Dispatcher\EventDispatcherInterface;
use Duktig\Core\Event\EventSimple;
use Psr\Log\LoggerInterface;

class ResolvableController extends BaseController
{
    protected $log;
    protected $testService;
    protected $eventDispatcher;
    
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        RendererInterface $renderer,
        ConfigInterface $config,
        LoggerInterface $log,
        TestService $testService,
        EventDispatcherInterface $eventDispatcher
    )
    {
        parent::__construct($responseFactory, $renderer, $config);

        $this->log = $log;
        $this->testService = $testService;
        $this->eventDispatcher = $eventDispatcher;
        
        $this->registerEvents();
    }
    
    protected function registerEvents()
    {
        $this->eventDispatcher->addListener(
            \Duktig\Test\Helpers\Event\EventTestLoadingPage::class,
            \Duktig\Test\Helpers\Event\ListenerEventTestLoadingPage::class
        );
    }
    
    public function paramsTestAction(string $uriParam1, string $uriParam2) : ResponseInterface
    {
        $uriParams = ['uriParam1' => $uriParam1, 'uriParam2' => $uriParam2];
        $queryParams = $this->getQueryParams();
        
        $this->writeResponseBody('Body set by ResolvableController::pageAction'
            ."\n".'Uri params: '.json_encode($uriParams)
            ."\n".'Query params: '.json_encode($queryParams)
        );
        return $this->response;
    }
    
    public function dependencyTestAction() : ResponseInterface
    {
        $this->writeResponseBody('Logging service provided instanceof '.get_class($this->log));
        return $this->response;
    }
    
    public function customServiceResolutionTestAction() : ResponseInterface
    {
        $this->writeResponseBody(
            'Custom service lazy loaded and injected with its own dependencies '
            .get_class($this->testService)
        );
        return $this->response;
    }
    
    public function templateAction() : ResponseInterface
    {
        $html = $this->render(['testString' => 'testing123']);
        $this->writeResponseBody($html);
        return $this->response;
    }
    
    public function templateMissingAction() : ResponseInterface
    {
        $html = $this->render(['testString' => 'testing123'], 'nonexistingTemplate.html.twig');
        $this->writeResponseBody($html);
        return $this->response;
    }
    
    public function routeMiddlewareAction() : ResponseInterface
    {
        return $this->response;
    }
    
    public function routeInvalidMiddlewareAction() : ResponseInterface
    {
        return $this->response;
    }
    
    public function testTriggerCustomEventAction() : ResponseInterface
    {
        $event = new \Duktig\Test\Helpers\Event\EventTestLoadingPage($this->response);
        $this->eventDispatcher->dispatch($event);
        return $this->response;
    }
    
    public function testTriggerCustomEventInConfigurationAction() : ResponseInterface
    {
        $this->eventDispatcher->dispatch(new EventSimple('duktig.tests.app.testEventRegisteredInConfiguration'));
        return $this->response;
    }
    
    public function testTriggerEventWithInvalidListenerAction() : ResponseInterface
    {
        $this->eventDispatcher->dispatch(new EventSimple('duktig.tests.app.testEventWhichWillGetAnInvalidListener'));
        return $this->response;
    }
}