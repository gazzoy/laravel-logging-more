<?php

namespace Gazzoy\LaravelLoggingMore\Loggers;

use Gazzoy\LaravelLoggingMore\Formatters\GeneralLogFormatter;
use Gazzoy\LaravelLoggingMore\Processors\LoggingMoreUidProcessor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Processor\IntrospectionProcessor;

class GeneralLogger extends AbstractLogger
{
    protected function getFormatter(): NormalizerFormatter
    {
        return new GeneralLogFormatter();
    }

    protected function getProcessors(): array
    {
        $userKey = Config::get('logging-more.user.key');
        $userProperty = Config::get('logging-more.user.property');

        return [
            app()->make(LoggingMoreUidProcessor::class),

            new IntrospectionProcessor(Level::Debug, [
                'Barryvdh\\Debugbar\\',
                'DebugBar\\',
            ]),

            function (LogRecord $record) use ($userKey, $userProperty)
            {
                $record['extra'][$userKey] = Auth::user()->$userProperty ?? null;

                return $record;
            },
        ];
    }
}
