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

class QueryLogger extends AbstractLogger
{
    private static bool $disableUserId = false;

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
                'Monolog\\',
                'Illuminate\\',
                'App\\Providers\\',
                'App\\Logging\\',
                'App\\Http\\Middleware\\ActionLogMiddleware',
                'Barryvdh\\Debugbar\\',
                'Bref\\LaravelBridge\\Http\\Middleware\\',
                'DebugBar\\',
                'Fruitcake\\Cors\\',
                'Laravel\\Horizon\\',
                'Laravel\\Octane\\',
                'Laravel\\Pulse\\',
                'Laravel\\Telescope\\',
                'Livewire\\Features\\SupportDisablingBackButtonCache\\',
                'Symfony\\Component\\Console\\',
            ]),

            function (LogRecord $record) use ($userKey, $userProperty)
            {
                $record['extra'][$userKey] = null;
                $record['extra']['channelActual'] = 'query';

                if (self::$disableUserId)
                {
                    return $record;
                }

                try
                {
                    self::$disableUserId = true;
                    $record['extra'][$userKey] = Auth::user()->$userProperty ?? null;
                }
                finally
                {
                    self::$disableUserId = false;
                }

                return $record;
            },
        ];
    }
}
