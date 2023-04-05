<?php
/**
 * DokuWiki Plugin Slack Notifier (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

use dokuwiki\HTTP\DokuHTTPClient;
use dokuwiki\Logger;

class action_plugin_slacknotifier extends DokuWiki_Action_Plugin
{

        var $_event = null;
        var $_payload = null;

        function register(Doku_Event_Handler $controller) {
                $controller->register_hook('IO_WIKIPAGE_WRITE', 'AFTER', $this, '_handle');
        }

        function _handle(Doku_Event $event) {

                // filter writes to attic
                if ($this->_attic_write($event)) return;

                // filter namespace
                if (!$this->_valid_namespace()) return;

                // filer event
                if (!$this->_set_event($event)) return;

                // set payload text
                $this->_set_payload_text();

                // set payload attachments
                $this->_set_payload_attachments();

                // submit payload
                $this->submitPayload($this->_payload);

        }

        private function _attic_write($event) {
                $filename = $event->data[0][0];
                if (strpos($filename, 'data/attic') !== false) return true;
        }

        private function _valid_namespace() {
                global $INFO;
                $validNamespaces = $this->getConf('namespaces');
                if (!empty($validNamespaces)) {
                        $validNamespacesArr = explode(',', $validNamespaces);
                        $thisNamespaceArr = explode(':', $INFO['namespace']);
                        return in_array($thisNamespaceArr[0], $validNamespacesArr);
                } else {
                        return true;
                }
        }

        private function _set_event($event) {
                global $INFO;
                $data = $event->data;
                $contents = $data[0][1];
                $newRev = $data[3];
                $oldRev = $INFO['meta']['last_change']['date'];
                if (!empty($contents) && empty($newRev) && empty($oldRev) && $this->getConf('notify_create') == 1) {
                        $this->_event = 'create';
                        return true;
                } elseif (!empty($contents) && empty($newRev) && !empty($oldRev) && $this->getConf('notify_edit') == 1) {
                        $this->_event = 'edit';
                        return true;
                } elseif (empty($contents) && empty($newRev) && $this->getConf('notify_delete') == 1) {
                        $this->_event = 'delete';
                        return true;
                } else {
                        return false;
                }
        }

        private function _set_payload_text() {
                global $INFO;
                switch ($this->_event) {
                        case 'create':
                                $event = "created";
                                break;
                        case 'edit':
                                $event = "updated";
                                break;
                        case 'delete':
                                $event = "removed";
                                break;
                }
                $user = $INFO['userinfo']['name'];
                $link = $this->_get_url();
                $page = $INFO['id'];
                $title = "{$user} {$event} page <{$link}|{$page}>";
                /* Searching changelogs yields previous revisions for
                 * created pages that had been deleted, however we'll
                 * use last_change which ignores these (so we won't
                 * show changes for created pages even if a previous
                 * revision exists)
                 */
                /*
                $changelog = new PageChangeLog($ID);
                $revArr = $changelog->getRevisions(0, 1);
                $oldRev = (count($revArr) == 1) ? $revArr[0] : null;
                */
                if ($this->_event != 'delete') {
                        $oldRev = $INFO['meta']['last_change']['date'];
                        if (!empty($oldRev)) {
                                $diffURL = $this->_get_url($oldRev);
                                $title .= " (<{$diffURL}|Compare changes>)";
                        }
                }
                $this->_payload = array("text" => $title);
        }

        private function _set_payload_attachments() {
                global $INFO;
                global $SUM;
                $user = $INFO['userinfo']['name'];
                if ($this->getConf('show_summary') == 1 && !empty($SUM)) {
                        $this->_payload['attachments'] = array(array(
                                "fallback"      => "Change summary",
                                "title"         => "Summary",
                                "text"          => "{$SUM}\n- {$user}"
                        ));
                }
        }

        private function _get_url($oldRev=null) {
                global $conf;
                global $INFO;
                $page = $INFO['id'];
                if (($conf['userewrite'] == 1 || $conf['userewrite'] == 2) && $conf['useslash'] == true) {
                        return str_replace(":", "/", $page);
                }
                switch($conf['userewrite']) {
                        case 0:
                                $url = DOKU_URL . "doku.php?id={$page}";
                                break;
                        case 1:
                                $url = DOKU_URL . $page;
                                break;
                        case 2:
                                $url = DOKU_URL . "doku.php/{$page}";
                                break;
                }
                if (!empty($oldRev)) {
                        switch($conf['userewrite']) {
                                case 0:
                                        $url .= "&do=diff&rev={$oldRev}";
                                        break;
                                case 1:
                                case 2:
                                        $url .= "?do=diff&rev={$oldRev}";
                                        break;
                        }
                }
                return $url;
        }

    private function submitPayload(array $payload)
    {
        $http = new DokuHTTPClient();
        $http->headers['Content-Type'] = 'application/json';
        // we do single ops here, no need for keep-alive
        $http->keep_alive = false;

        $url = $this->getConf('webhook');
        $result = $http->post($url, array('payload' => json_encode($payload)));
        if ($result !== 'ok') {
            $ctx = [
                'resp_body' => $http->resp_body,
                'result' => $result,
                'http_error' => $http->error,
            ];
            Logger::error("Error posting to Slack", $ctx, __FILE__, __LINE__);
        }
    }
}
