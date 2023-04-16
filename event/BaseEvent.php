<?php

namespace dokuwiki\plugin\slacknotifier\event;

use dokuwiki\Extension\Event;
use InvalidArgumentException;

abstract class BaseEvent
{
    /** @var array */
    private $data;

    public function __construct(Event $event)
    {
        $this->data = $event->data;
    }

    public function __get(string $name)
    {
        if (!array_key_exists($name, $this->data)) {
            throw new InvalidArgumentException("Invalid property: $name");
        }

        return $this->data[$name];
    }
}
