<?php
namespace Duktig\Test\Helpers\Event;

use Duktig\Core\Event\ListenerInterface;
use Duktig\Core\Event\EventInterface;

class ListenerEventTestLoadingPage implements ListenerInterface
{
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Event\ListenerInterface::handle()
     */
    public function handle(EventInterface $event) : void
    {
        $event->getResponse()->getBody()->write(
            'EventTestListener has altered the response when EventTest was triggered'
        );
    }
}