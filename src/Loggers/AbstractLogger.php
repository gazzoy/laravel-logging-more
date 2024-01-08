<?php

namespace Gazzoy\LaravelLoggingMore\Loggers;

abstract class AbstractLogger
{
    abstract protected function getProcessors(): array;
}
