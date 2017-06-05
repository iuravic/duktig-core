<?php
namespace Duktig\Core\Http;

use Duktig\Core\Http\ResponseSenderInterface;
use Psr\Http\Message\ResponseInterface;
use Duktig\Core\Event\CoreEvents\EventAfterAppReponseHeadersSending;
use Duktig\Core\Event\Dispatcher\EventDispatcherInterface;

/**
 * This class dispatches the core event EventAfterAppReponseHeadersSending right
 * after the response headers have been sent.
 */
class ResponseSender implements ResponseSenderInterface
{
    protected $eventDispatcher;
    
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Http\ResponseSenderInterface::sendResponse()
     */
    public function sendResponse(ResponseInterface $response) : void
    {
        if (!headers_sent()) {
            $this->sendStatusLine($response);
            $this->sendHeaders($response);
        }
        $this->eventDispatcher->dispatch(new EventAfterAppReponseHeadersSending($response));
        $this->sendBody($response);
    }
    
    protected function sendStatusLine(ResponseInterface $response) : void
    {
        $this->phpHeader(
            sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ),
            true,
            $response->getStatusCode()
        );
    }
    
    protected function sendHeaders(ResponseInterface $response) : void
    {
        foreach ($response->getHeaders() as $key => $values) {
            foreach ($values as $value) {
                $this->phpHeader(
                    sprintf('%s: %s', $key, $value),
                    false,
                    $response->getStatusCode()
                );
            }
        }
    }
    
    protected function sendBody(ResponseInterface $response) : void
    {
        echo $response->getBody()->__toString();
    }
    
    /**
     * Wrapper for the PHP header() method.
     * 
     * @param string $string
     * @param bool $replace [optional]
     * @param int $httpResponseCode [optional]
     */
    protected function phpHeader(string $string, bool $replace = null, int $httpResponseCode = null) : void
    {
        header($string, $replace, $httpResponseCode);
    }
}