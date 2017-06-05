<?php
namespace Duktig\Core\Exception\Handler;

use Duktig\Core\Exception\Handler\HandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Http\Factory\ResponseFactoryInterface;
use Duktig\Core\Config\ConfigInterface;
use Duktig\Core\View\RendererInterface;
use Duktig\Core\Exception\{
    TemplateNotFoundException,
    HttpException
};
use Psr\Log\LoggerInterface;
use Duktig\Core\Http\ResponseSenderInterface;

class Handler implements HandlerInterface
{
    protected $responseFactory;
    protected $config;
    protected $renderer;
    protected $log;
    protected $responseSender;
    
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        ConfigInterface $config,
        RendererInterface $renderer,
        LoggerInterface $logger,
        ResponseSenderInterface $responseSender
    )
    {
        $this->responseFactory = $responseFactory;
        $this->config = $config;
        $this->renderer = $renderer;
        $this->log = $logger;
        $this->responseSender = $responseSender;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Exception\Handler\HandlerInterface::report()
     */
    public function report(\Throwable $e) : void
    {
        $this->log->error($e);
    }

    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Exception\Handler\HandlerInterface::register()
     */
    public function register() : void
    {
        if ($this->getConfigParam('env') == 'prod') {
            ini_set('display_errors', 'Off');
        } else {
            ini_set('display_errors', 'On');
        }
        error_reporting(E_ALL);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }
    
    /**
     * Error handler.
     * 
     * @param int $severity
     * @param string $message
     * @param string $filename [optional]
     * @param int $lineno [optional]
     * @param array $context [optional]
     * @throws \ErrorException
     */
    public function handleError(int $severity, string $message, 
        string $filename = null, int $lineno = 0, array $context = []
    ) : void
    {
        throw new \ErrorException($message, 0, $severity, $filename, $lineno);
    }
    
    /**
     * Exception handler.
     * 
     * @param \Throwable $e
     * @throws \Throwable
     */
    public function handleException(\Throwable $e) : void
    {
        $this->report($e);
        if ($this->getConfigParam('env') != 'prod') {
            throw $e;
        }
        $response = $this->throwableToResponse($e);
        $this->responseSender->sendResponse($response);
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Exception\Handler\HandlerInterface::throwableToResponse()
     * 
     * If a template is not available for this throwable instance, it sets a
     *      simple default HTML in the response body.
     * 
     * @param \Throwable $e
     * @return ResponseInterface
     */
    public function throwableToResponse(\Throwable $e) : ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
        } else {
            $statusCode = $e->getCode() != 0 ? $e->getCode() : 500;
        }
        
        $response = $response->withStatus($statusCode);
        
        try {
            $html = $this->getHtmlFromTemplate($e);
        } catch (\Throwable $e) {
            $html = $this->getHtmlForException($e);
        }
        
        $response->getBody()->write($html);
        
        return $response;
    }
    
    /**
     * Searches for the most specific template it can find for the given throwable
     * and returns the HTML.
     * 
     * The template for the \Throwable is searched and determined i the following
     * way:
     *   - all error templates are located in a sub dir for error templates which
     *     is defined in config
     *   - if an HttpException is thrown, it first looks for a template with 
     *     the error code as its name, ie. "404{{suffix}}", "500{{suffix}}", etc.
     *   - for all other exception types, and if the template is not found as the
     *     error code for the HttpException exception, it looks for a template 
     *     with the name equal to the exception class name (its short, 
     *     non-fully-qualified class name)
     *   - if a template is not found in the previous steps, it searches for a
     *     generic error template by a name which is defined in config, ie. 
     *     "Error{{suffix}}"
     *
     * The renderer service itself should know the paths of the templates, and 
     * by default those directories are:
     *   - first looks in the application template dir for custom templates,
     *   - then looks in the framework core dir for the default templates.
     *
     * @param \Exception $e
     * @throws TemplateNotFoundException Thrown when all options fail, which should not happen
     * @return string $html
     */
    protected function getHtmlFromTemplate(\Throwable $e) : string
    {
        $suffix = $this->getConfigParam('view')['templateSuffix'];
        $subDir = $this->getConfigParam('view')['templateErrorSubDirApp'];
        $templateErrorGeneric = $this->getConfigParam('view')['templateErrorGeneric'];
        if ($e instanceof HttpException) {
            $templateNameByStatusCode = $subDir . '/' . $e->getStatusCode() . $suffix;
            if ($html = $this->renderTemplate($templateNameByStatusCode)) {
                return $html;
            }
        }
        
        $templateNameByExceptionName = $subDir . '/'. (new \ReflectionClass($e))->getShortName() . $suffix;
        if ($html = $this->renderTemplate($templateNameByExceptionName)) {
            return $html;
        }
        
        $templateErrorGeneric = $subDir . '/'. $templateErrorGeneric . $suffix;
        if ($html = $this->renderTemplate($templateErrorGeneric)) {
            return $html;
        }
        
        $exceptionClass = get_class($e);
        throw new TemplateNotFoundException(
            "A template for {$exceptionClass} exception should have been located but wasn't."
        );
    }
    
    /**
     * Render the template with the renderer.
     * 
     * @param string $template
     * @return string|NULL $html
     */
    protected function renderTemplate(string $template) : ?string
    {
        return $this->renderer->render($template);
    }
    
    /**
     * Returns a simple HTML response body for a throwable.
     * 
     * @param \Throwable $e
     * @return string
     */
    protected function getHtmlForException(\Throwable $e) : string
    {
        return <<<EOF
<!DOCTYPE html>
<html>
    <head>
        <meta name="robots" content="noindex,nofollow" />
        <title>Error</title>
    </head>
    <body>
        Whoops, looks like something went wrong.
    </body>
</html>
EOF;
    }
        
    /**
     * Gets param from config service
     * 
     * @param string $param
     * @return mixed
     */
    protected function getConfigParam(string $param)
    {
        return $this->config->getParam($param);
    }
}