<?php

namespace dokuwiki\plugin\slacknotifier\helper;

use dokuwiki\Extension\Event;
use InvalidArgumentException;

/**
 * @property string|null $summary
 * @property string $id
 * @property int|false $oldRevision
 * @property int $newRevision
 * @link https://www.dokuwiki.org/devel:event:common_wikipage_save
 */
class Payload
{
    private const EVENT_TYPE = [
        DOKU_CHANGE_TYPE_EDIT => 'edit',
        DOKU_CHANGE_TYPE_MINOR_EDIT => 'edit minor',
        DOKU_CHANGE_TYPE_CREATE => 'create',
        DOKU_CHANGE_TYPE_DELETE => 'delete',
    ];

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

    private static function isValidEvent(?string $eventType, Config $config): bool
    {
        if ($eventType === 'create' && $config->notify_create) {
            return true;
        } elseif ($eventType === 'edit' && $config->notify_edit) {
            return true;
        } elseif ($eventType === 'edit minor' && $config->notify_edit && $config->notify_edit_minor) {
            return true;
        } elseif ($eventType === 'delete' && $config->notify_delete) {
            return true;
        }

        return false;
    }
}
