# laravel-logging-more

This package provides more logging for Laravel.

## Feature
- action logs as JSON
- query logs as JSON

## Requirements
- PHP >= 8.0
- Laravel >= 9

## Installation
You can include this package via Composer:

`composer require gazzoy/laravel-logging-more`

## Usage

Before you use this package, you have to publish the config to your application:

`php artisan vendor:publish --tag "logging-more-config"`

app/Http/Kernel.php
```php
...
protected $middlewareGroups = [
        'web' => [
            ...
            \Gazzoy\LaravelLoggingMore\Middleware\ActionLogMiddleware::class,
        ],
        ...
];
...
```

config/logging.php
```php
    ...
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'tap' => [Gazzoy\LaravelLoggingMore\Loggers\LineLogger::class],
        ],
    ...
        'general' => [
            'driver' => 'daily',
            'path' => storage_path('logs/general.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'replace_placeholders' => true,
            'tap' => [Gazzoy\LaravelLoggingMore\Loggers\GeneralLogger::class],
        ],

        'action' => [
            'driver' => 'daily',
            'path' => storage_path('logs/action.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'replace_placeholders' => true,
            'tap' => [Gazzoy\LaravelLoggingMore\Loggers\ActionLogger::class],
        ],

        'query' => [
            'driver' => 'daily',
            'path' => storage_path('logs/query.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
            'replace_placeholders' => true,
            'tap' => [Gazzoy\LaravelLoggingMore\Loggers\QueryLogger::class],
        ],
    ...
```

## Test

```shell
composer install
./vendor/bin/pest 
```
