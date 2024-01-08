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

## Test

```shell
composer install
./vendor/bin/pest 
```
