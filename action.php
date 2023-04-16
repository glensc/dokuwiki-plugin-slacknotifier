<?php
/**
 * DokuWiki Plugin Slack Notifier (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 */

use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;
use dokuwiki\HTTP\DokuHTTPClient;
use dokuwiki\Logger;
use dokuwiki\plugin\slacknotifier\helper\Config;
use dokuwiki\plugin\slacknotifier\helper\Context;
use dokuwiki\plugin\slacknotifier\helper\Formatter;
use dokuwiki\plugin\slacknotifier\helper\Payload;

class action_plugin_slacknotifier extends DokuWiki_Action_Plugin
{
    public function register(EventHandler $controller): void
    {
        $controller->register_hook('COMMON_WIKIPAGE_SAVE', 'AFTER', $this, 'handleSave');
    }

    public function handleSave(Event $event): void
    {
        $config = new Config($this);
        if (!$this->isValidNamespace($config->namespaces)) {
            return;
        }

        $payload = Payload::fromEvent($event, $config);
        if (!$payload) {
            return;
        }

        $formatter = new Formatter($config);
        $formatted = $formatter->format($payload, new Context());

        $this->submitPayload($formatted);
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

    private function submitPayload(array $payload): void
    {
        $http = new DokuHTTPClient();
        $http->headers['Content-Type'] = 'application/json';
        // we do single ops here, no need for keep-alive
        $http->keep_alive = false;

        $url = $this->getConf('webhook');
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
