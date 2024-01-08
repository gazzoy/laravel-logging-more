<?php

namespace Gazzoy\LaravelLoggingMore\Loggers;

use Illuminate\Log\Logger;
use Monolog\Formatter\NormalizerFormatter;

abstract class AbstractLogger
{
    public function __invoke(Logger $logger): void
    {
        $processors = $this->getProcessors();

        foreach ($logger->getHandlers() as $handler)
        {
            $handler->setFormatter($this->getFormatter()); // @phpstan-ignore-line
            foreach ($processors as $eachProcessor)
            {
                $handler->pushProcessor($eachProcessor); // @phpstan-ignore-line
            }
        }
    }

    abstract protected function getFormatter(): NormalizerFormatter;

    abstract protected function getProcessors(): array;
}
