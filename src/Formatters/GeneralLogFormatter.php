<?php

namespace Gazzoy\LaravelLoggingMore\Formatters;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

class GeneralLogFormatter extends NormalizerFormatter
{
    public const SIMPLE_DATE = 'Y-m-d\TH:i:s.uP';

    /**
     * Formats a log record.
     *
     * @param LogRecord $record A record to format
     *
     * @return mixed The formatted record
     */
    public function format(LogRecord $record)
    {
        $record = parent::format($record);

        $userKey = Config::get('logging-more.user.key');

        $keysFromTo = [
            'channelActual' => 'channel',
            'uid' => 'uid',
            $userKey => $userKey,
        ];
        $exceptKeys = [];
        foreach ($keysFromTo as $eachKeyFrom => $eachKeyTo)
        {
            if (Arr::has($record, "extra.{$eachKeyFrom}"))
            {
                $record[$eachKeyTo] = $record['extra']["{$eachKeyFrom}"];
                $exceptKeys[] = $eachKeyFrom;
            }
        }
        $record['extra'] = Arr::except($record['extra'], $exceptKeys);

        // note: make array as object for AWS Athena compatibility
        $this->addJsonEncodeOption(JSON_FORCE_OBJECT);

        return $this->toJson($this->formatRecord($record)) . PHP_EOL;
    }

    protected function formatRecord($record)
    {
        $userKey = Config::get('logging-more.user.key');

        return [
            'timestamp' => $record['datetime'] ?? gmdate('c'),
            'channel' => $record['channel'],
            'uid' => $record['uid'] ?? null,
            $userKey => $record[$userKey] ?? null,
            'message' => $record['message'],
            'host' => gethostname(),
            'env' => config('app.env'),
            'level' => $record['level_name'],
            'extra' => $record['extra'],
            'context' => $record['context'],
        ];
    }
}
