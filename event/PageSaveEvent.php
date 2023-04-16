<?php

namespace dokuwiki\plugin\slacknotifier\event;

use dokuwiki\Extension\Event;
use dokuwiki\plugin\slacknotifier\helper\Config;

/**
 * @property string $changeType
 * @property string|null $summary
 * @property string $id
 * @property int|false $oldRevision
 * @property int $newRevision
 * @link https://www.dokuwiki.org/devel:event:common_wikipage_save
 */
class PageSaveEvent extends BaseEvent
{
    private const EVENT_TYPE = [
        DOKU_CHANGE_TYPE_EDIT => 'edit',
        DOKU_CHANGE_TYPE_MINOR_EDIT => 'edit minor',
        DOKU_CHANGE_TYPE_CREATE => 'create',
        DOKU_CHANGE_TYPE_DELETE => 'delete',
    ];

    /** @var string|null */
    public $eventType;

    public static function fromEvent(Event $rawEvent, Config $config): ?self
    {
        $changeType = $rawEvent->data['changeType'];
        $eventType = self::EVENT_TYPE[$changeType] ?? null;
        if (!self::isValidEvent($eventType, $config)) {
            return null;
        }

        $event = new static($rawEvent);
        $event->eventType = $eventType;

        return $event;
    }

    public function getEventType(): ?string
    {
        return self::EVENT_TYPE[$this->changeType] ?? null;
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
