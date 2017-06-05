<?php
namespace Duktig\Test\Helpers\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;

class TestRouteSpecificMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     * @see \Interop\Http\Middleware\MiddlewareInterface::process()
     */
    public function process(ServerRequestInterface $request, DelegateInterface $next)
    {
        $response = $next->process($request);
        $response->getBody()->write('Response body modified by TestRouteSpecificMiddleware');
        return $response;
    }
}