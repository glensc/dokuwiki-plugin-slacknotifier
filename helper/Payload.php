<?php

namespace dokuwiki\plugin\slacknotifier\helper;

use dokuwiki\Extension\Event;
use InvalidArgumentException;

/**
 * @property string|null $summary
 * @property string $id
 * @property int $oldRevision
 */
class Payload
{
    private const EVENT_TYPE = array(
        'E' => 'edit',
        'C' => 'create',
        'D' => 'delete',
    );

    /** @var string|null */
    public $eventType;
    /** @var array */
    private $data;

    public static function fromEvent(Event $event, Config $config): ?self
    {
        $changeType = $event->data['changeType'];
        $eventType = self::EVENT_TYPE[$changeType] ?? null;
        if (!self::isValidEvent($eventType, $config)) {
            return null;
        }

        $payload = new static();
        $payload->summary = $event->data['summary'] ?: null;
        $payload->eventType = $eventType;
        $payload->data = $event->data;

        return $payload;
    }

    public function __get(string $name)
    {
        if (!array_key_exists($name, $this->data)) {
            throw new InvalidArgumentException("Invalid property: $name");
        }

        return $this->data[$name];
    }

    private static function isValidEvent(string $eventType, Config $config): bool
    {
        if ($eventType === 'create' && $config->notify_create) {
            return true;
        } elseif ($eventType === 'edit' && $config->notify_edit) {
            return true;
        } elseif ($eventType === 'delete' && $config->notify_delete) {
            return true;
        }

        return false;
    }
}
