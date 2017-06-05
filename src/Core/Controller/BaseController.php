<?php
namespace Duktig\Core\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\Factory\ResponseFactoryInterface;
use Duktig\Core\View\RendererInterface;
use Duktig\Core\Config\ConfigInterface;

abstract class BaseController
{
    protected $request;
    protected $response;
    protected $renderer;
    protected $queryParams;
    protected $config;
    protected $templateSuffix;
    
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        RendererInterface $renderer,
        ConfigInterface $config
    )
    {
        $this->response = $responseFactory->createResponse();
        $this->renderer = $renderer;
        $this->config= $config;
        $this->templateSuffix = $this->config->getParam('view')['templateSuffix'];
        $this->addResponseHeader('Content-Type', 'text/html');
    }
    
    /**
     * Sets the request.
     * 
     * @param ServerRequestInterface $request
     * @return void
     */
    public function setRequest(ServerRequestInterface $request) : void
    {
        $this->request = $request;
    }
    
    /**
     * Parse the query params.
     * 
     * @return void
     */
    protected function parseQueryParams() : void
    {
        $queryParams = [];
        $queryString = $this->request->getUri()->getQuery();
        parse_str($queryString, $queryParams);
        $this->queryParams = $queryParams;
    }
    
    /**
     * Gets a query param.
     * 
     * @param string $param
     * @return string|NULL
     */
    protected function getQueryParam(string $param) : ?string
    {
        if ($this->queryParams === null) {
            $this->parseQueryParams();
        }
        return $this->queryParams[$param] ?? null;
    }
    
    /**
     * Gets all the query params.
     * 
     * @return array
     */
    protected function getQueryParams() : array
    {
        if ($this->queryParams === null) {
            $this->parseQueryParams();
        }
        return $this->queryParams;
    }
    
    /**
     * Sets the response status.
     * 
     * @param int $code
     * @param string $reasonPhrase [optional]
     */
    protected function setResponseStatus(int $code, string $reasonPhrase = '') : void
    {
        $this->response = $this->response->withStatus($code, $reasonPhrase);
    }
    
    /**
     * Adds a response header.
     * 
     * @param string $name
     * @param string $value [optional]
     */
    protected function addResponseHeader(string $name, string $value = '') : void
    {
        $this->response = $this->response->withAddedHeader($name, $value);
    }
    
    /**
     * Writes to the response body.
     * 
     * @param string $body
     */
    protected function writeResponseBody(string $body) : void
    {
        $this->response->getBody()->write($body);
    }
    
    /**
     * @param array $data Template data
     * @param string|null $template [optional] If not provided, the conventional
     *      template naming is used, ie. default template for 
     *      ControllerExample::someAction inside the template directory will be 
     *      'Example/someAction{{suffix}}'
     * @return string The rendered html
     */
    protected function render(array $data, string $template = null) : string
    {
        if (is_null($template)) {
            $controllerMethodName = debug_backtrace()[1]['function'];
            $fullyQualifiedClassName = debug_backtrace()[1]['class'];
            $fullyQualifiedClassNameArr = explode('\\', $fullyQualifiedClassName);
            $controllerClassName = $fullyQualifiedClassNameArr[count($fullyQualifiedClassNameArr)-1];
            $template = $controllerClassName . '/' . $controllerMethodName . $this->templateSuffix;
        }
        return $this->renderer->render($template, $data);
    }
}