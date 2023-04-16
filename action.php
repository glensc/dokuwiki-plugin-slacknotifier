<?php
/**
 * DokuWiki Plugin Slack Notifier (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 */

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;
use dokuwiki\HTTP\DokuHTTPClient;
use dokuwiki\Logger;
use dokuwiki\plugin\slacknotifier\event\PageSaveEvent;
use dokuwiki\plugin\slacknotifier\helper\Config;
use dokuwiki\plugin\slacknotifier\helper\Context;
use dokuwiki\plugin\slacknotifier\helper\Formatter;

class action_plugin_slacknotifier extends ActionPlugin
{
    public function register(EventHandler $controller): void
    {
        $controller->register_hook('COMMON_WIKIPAGE_SAVE', 'AFTER', $this, 'handleSave');
    }

    public function handleSave(Event $rawEvent): void
    {
        $config = new Config($this);
        if (!$this->isValidNamespace($config->namespaces)) {
            return;
        }

        $event = new PageSaveEvent($rawEvent);
        if (!$this->isValidEvent($event->getEventType(), $config)) {
            return;
        }

        $formatter = new Formatter($config);
        $formatted = $formatter->format($event, new Context());

        $this->submitPayload($config->webhook, $formatted);
    }

    private function isValidNamespace(?string $validNamespaces): bool
    {
        if (!$validNamespaces) {
            return true;
        }

        global $INFO;
        $validNamespaces = explode(',', $validNamespaces);
        $thisNamespace = explode(':', $INFO['namespace']);

        return in_array($thisNamespace[0], $validNamespaces, true);
    }

    private function isValidEvent(?string $eventType, Config $config): bool
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

    private function submitPayload(string $url, array $payload): void
    {
        $http = new DokuHTTPClient();
        $http->headers['Content-Type'] = 'application/json';
        // we do single ops here, no need for keep-alive
        $http->keep_alive = false;

        $result = $http->post($url, ['payload' => json_encode($payload)]);
        if ($result !== 'ok') {
            $ctx = [
                'resp_body' => $http->resp_body,
                'result' => $result,
                'http_error' => $http->error,
            ];
            Logger::error('Error posting to Slack', $ctx, __FILE__, __LINE__);
        }
    }
}
