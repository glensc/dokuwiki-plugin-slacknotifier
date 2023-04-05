<?php

namespace dokuwiki\plugin\slacknotifier\helper;

/**
 * Context of the edit.
 *
 * Things extracted from $INFO.
 *
 * @property ?string $username
 */
class Context
{
    public function __get($name)
    {
        $method = "get$name";
        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException("Invalid property: $name");
        }

        return $this->{$method}();
    }

    public function getUsername(): ?string
    {
        global $INFO;

        return $INFO['userinfo']['name'] ?? null;
    }
}
