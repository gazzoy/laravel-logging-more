<?php

namespace Gazzoy\LaravelLoggingMore\Formatters;

use Illuminate\Support\Facades\Config;

class ActionLogFormatter extends GeneralLogFormatter
{
    protected function formatRecord($record)
    {
        $url = null;
        if (isset($record['context']['url']))
        {
            $url = $record['context']['url'];
            unset($record['context']['url']);
        }

        if (
            !isset($record['context']['request'])
            || $record['context']['request'] === []
        )
        {
            $record['context']['request'] = (object) [];
        }

        $userKey = Config::get('logging-more.user.key');

        return [
            'timestamp' => $record['datetime'] ?? gmdate('c'),
            'channel' => $record['channel'],
            'uid' => $record['uid'] ?? null,
            $userKey => $record[$userKey] ?? null,
            'url' => $url,
            'message' => $record['message'],
            'host' => gethostname(),
            'env' => Config::get('app.env'),
            'level' => $record['level_name'],
            'extra' => $record['extra'],
            'context' => $record['context'],
        ];
    }
}
