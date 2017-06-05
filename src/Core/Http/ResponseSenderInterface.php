<?php
namespace Duktig\Core\Http;

use Psr\Http\Message\ResponseInterface;

interface ResponseSenderInterface
{
    /**
     * Sends response to the browser.
     * 
     * @param ResponseInterface $response
     */
    public function sendResponse(ResponseInterface $response) : void;
}