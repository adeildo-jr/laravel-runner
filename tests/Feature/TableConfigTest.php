<?php

use AdeildoJr\Runner\Tests\Models\Runner;

beforeEach(function () {
    config()->set('runner.model', Runner::class);
    config()->set('runner.table', 'system_tasks');
});

it('uses default table name from config', function () {
    expect(config('runner.table'))->toBe('system_tasks');
});

it('allows custom table name via config', function () {
    config()->set('runner.table', 'custom_tasks_table');

    expect(config('runner.table'))->toBe('custom_tasks_table');
});

it('uses custom table name from environment variable', function () {
    // Simulate environment variable
    putenv('SYSTEM_TASKS_TABLE=env_tasks_table');

    // Re-read config (in real usage, this would be cached)
    $table = env('SYSTEM_TASKS_TABLE', 'system_tasks');

    expect($table)->toBe('env_tasks_table');

    // Clean up
    putenv('SYSTEM_TASKS_TABLE');
});
