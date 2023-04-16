<?php

namespace dokuwiki\plugin\slacknotifier\event;

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
    const TYPE_RENAME = 'rename';

    private const EVENT_TYPE = [
        DOKU_CHANGE_TYPE_EDIT => 'edit',
        DOKU_CHANGE_TYPE_MINOR_EDIT => 'edit minor',
        DOKU_CHANGE_TYPE_CREATE => 'create',
        DOKU_CHANGE_TYPE_DELETE => 'delete',
        self::TYPE_RENAME => 'rename',
    ];

    public function getEventType(): ?string
    {
        return self::EVENT_TYPE[$this->changeType] ?? null;
    }

    public function isCreate(): bool
    {
        return $this->changeType === DOKU_CHANGE_TYPE_CREATE;
    }

    public function isDelete(): bool
    {
        return $this->changeType === DOKU_CHANGE_TYPE_DELETE;
    }
}
