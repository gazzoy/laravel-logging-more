<?php

namespace Gazzoy\LaravelLoggingMore\Loggers;

use Gazzoy\LaravelLoggingMore\Loggers\AbstractLogger;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Level;
use Monolog\Processor\IntrospectionProcessor;

class LineLogger extends AbstractLogger
{
    protected function getFormatter(): NormalizerFormatter
    {
        return new LineFormatter(
            '[%datetime%] %channel%.%level_name%: (%extra.class%#%extra.function%:%extra.file%@%extra.line%) %message% %context% %extra%' . PHP_EOL,
            'Y-m-d H:i:s'
        );
    }

    protected function getProcessors(): array
    {
        return [
            new IntrospectionProcessor(Level::Debug, [
                'Illuminate\\',
            ]),
        ];
    }
}
