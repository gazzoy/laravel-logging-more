<?php

use Gazzoy\LaravelLoggingMore\Formatters\GeneralLogFormatter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Monolog\DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use Tests\Fixtures\TestUser;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can format batch', function ()
{
    Config::set('logging-more.user.key', 'userId');
    Config::set('logging-more.user.property', 'id');

    $user = new TestUser([
        'id' => 1,
        'code' => 'user1',
    ]);

    $formatter = new GeneralLogFormatter();

    $record = new LogRecord(
        message: 'dummy-message',
        channel: 'dummy-channel',
        level: Level::Debug,
        datetime: new DateTimeImmutable(true),
        context: [],
        extra: [
            'uid' => 'extra-content-uid',
            'file' => 'extra-content-file',
            'line' => 'extra-content-line',
            'class' => 'extra-content-class',
            'callType' => 'extra-content-callType',
            'function' => 'extra-content-function',
            'userId' => $user->id,
        ],
    );
    $records = [
        $record,
    ];

    $actual = $formatter->formatBatch($records);
    expect(Arr::has($actual, '0'))->toBeTrue();
    $logData = json_decode($actual[0], true);
    expect($logData['timestamp'])->not->toBeNull();
    expect($logData['host'])->not->toBeNull();
    expect($logData['uid'])->not->toBeNull();
    expect($logData['userId'])->toBe($user->id);
    expect($logData['channel'])->toBe('dummy-channel');
    expect($logData['message'])->toBe('dummy-message');
    expect($logData['level'])->toBe('DEBUG');
    expect(is_array($logData['extra']))->toBeTrue();
    expect(Arr::has($logData, 'extra.file'))->toBeTrue();
    expect(Arr::has($logData, 'extra.line'))->toBeTrue();
    expect(Arr::has($logData, 'extra.class'))->toBeTrue();
    expect(Arr::has($logData, 'extra.callType'))->toBeTrue();
    expect(Arr::has($logData, 'extra.function'))->toBeTrue();
});
