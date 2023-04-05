<?php

namespace dokuwiki\plugin\slacknotifier\helper;

use dokuwiki\Extension\PluginInterface;

/**
 * A class to provide lazy access to plugin config
 *
 * @property string $namespaces
 * @property bool $notify_create
 * @property bool $notify_edit
 * @property bool $notify_delete
 * @property bool $show_summary
 */
class Config
{
    /** @var PluginInterface */
    private $plugin;

    public function __construct(PluginInterface $plugin)
    {

        $this->plugin = $plugin;
    }

    public function __get($name)
    {
        return $this->plugin->getConf($name);
    }
}
