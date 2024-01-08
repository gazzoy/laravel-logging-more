<?php

namespace Gazzoy\LaravelLoggingMore\Loggers;

use Gazzoy\LaravelLoggingMore\Formatters\ActionLogFormatter;
use Gazzoy\LaravelLoggingMore\Processors\LoggingMoreUidProcessor;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Processor\IntrospectionProcessor;

class ActionLogger extends AbstractLogger
{
    public function __invoke(Logger $logger): void
    {
        $formatter = new ActionLogFormatter();
        $processors = $this->getProcessors();

        foreach ($logger->getHandlers() as $handler)
        {
            $handler->setFormatter($formatter); // @phpstan-ignore-line
            foreach ($processors as $eachProcessor)
            {
                $handler->pushProcessor($eachProcessor); // @phpstan-ignore-line
            }
        }
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
            ]),

            function (LogRecord $record) use ($userKey, $userProperty)
            {
                $record['extra'][$userKey] = Auth::user()->$userProperty ?? null;
                $record['extra']['channelActual'] = 'action';

                return $record;
            },
        ];
    }
}
