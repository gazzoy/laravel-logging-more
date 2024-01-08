<?php

use Gazzoy\LaravelLoggingMore\Providers\QueryLogServiceProvider;
use Illuminate\Contracts\Foundation\Application;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can detect column name from SQL', function ()
{
    $appMock = \Mockery::mock(Application::class);
    $QueryLogServiceProvider = new QueryLogServiceProvider($appMock);

    expect($QueryLogServiceProvider->detectColumnNameFromSQL('select * from `sessions` where `id` = ? limit 1'))->toBe('id');
    expect($QueryLogServiceProvider->detectColumnNameFromSQL('update `sessions` set `payload` = ?, `last_activity` = ?, `user_id` = ?, `ip_address` = ?, `user_agent` = ? where `id` = ?'))->toBe('payload');
    expect($QueryLogServiceProvider->detectColumnNameFromSQL("update `sessions` set `payload` = 'foobar', `last_activity` = ?, `user_id` = ?, `ip_address` = ?, `user_agent` = ? where `id` = ?"))->toBe('last_activity');
    expect($QueryLogServiceProvider->detectColumnNameFromSQL("select * from `users` where `password_legacy` is not null and `email` = 'User2@mailaddress.com' and `invalid` = 0 and `deleted_at` = null and `user_type` = 1 and `password_legacy` = ? limit 1"))->toBe('password_legacy');
    expect($QueryLogServiceProvider->detectColumnNameFromSQL('select * from `sessions` where `id` in ? limit 1'))->toBeNull();
});
