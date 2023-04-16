<?php

namespace dokuwiki\plugin\slacknotifier\event;

use dokuwiki\Extension\Event;
use InvalidArgumentException;

/**
 * @property string $src_id
 * @property string $dst_id
 * @link https://www.dokuwiki.org/plugin:move#for_plugin_authors
 */
class PageMoveEvent extends BaseEvent
{

}
