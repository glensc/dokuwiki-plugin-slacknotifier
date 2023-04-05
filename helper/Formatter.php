<?php

namespace dokuwiki\plugin\slacknotifier\helper;

class Formatter
{
    /** @var Config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function format(Payload $payload, Context $context): array
    {
        $actionMap = [
            'create' => 'created',
            'edit' => 'updated',
            'edit minor' => 'updated (minor edit)',
            'delete' => 'removed',
        ];
        $action = $actionMap[$payload->eventType] ?? null;
        $username = $context->username ?: 'Anonymous';
        $page = $payload->id;
        $link = $this->buildUrl($page, $payload->newRevision);
        $title = "{$username} {$action} page <{$link}|{$page}>";
        if ($payload->eventType !== 'delete') {
            $oldRev = $payload->oldRevision;
            if ($oldRev) {
                $diffURL = $this->buildUrl($page, $payload->newRevision, $payload->oldRevision);
                $title .= " (<{$diffURL}|Compare changes>)";
            }
        }

        $formatted = ['text' => $title];
        if ($payload->summary && $this->config->show_summary) {
            $formatted['attachments'] = [
                [
                    'fallback' => 'Change summary',
                    'title' => 'Summary',
                    'text' => "{$payload->summary}\n- {$username}",
                ],
            ];
        }

        return $formatted;
    }

    private function buildUrl(string $page, int $rev, ?int $oldRev = null): ?string
    {
        $urlParameters = $oldRev ? "do=diff&rev2[0]=$oldRev&rev2[1]=$rev" : "rev=$rev";

        return wl($page, $urlParameters, true, '&');
    }
}
