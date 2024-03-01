# DokuWiki Slack Notifier

A DokuWiki plugin that notifies a Slack channel room of wiki edits.

![Example notification](example.png)

![Example rename notification](example_rename.png)

## Install

Download the latest release from [Tags] page and install the plugin using the
[Plugin Manager]. Refer to [Plugins] on how to install plugins manually.

[Tags]: https://github.com/glensc/dokuwiki-plugin-slacknotifier/tags
[Plugin Manager]: https://www.dokuwiki.org/plugin:plugin
[Plugins]: https://www.dokuwiki.org/plugins

## Configure

1. Create an Incoming Webhook on slack: https://api.slack.com/messaging/webhooks#create_a_webhook

2. Enter the webhook into the slacknotifier configuration section in DokuWiki's Configuration Settings

## Root Namespace

To include the root namespace, simply put a `:` in the namespace field in the config.
