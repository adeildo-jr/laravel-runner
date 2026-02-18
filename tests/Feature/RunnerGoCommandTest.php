<?php

use AdeildoJr\Runner\Tests\Models\Runner;

beforeEach(function () {
    config()->set('runner.model', Runner::class);
});

it('dispatches job when running all runners', function () {
    \Illuminate\Support\Facades\Queue::fake();

    $this->artisan('runner:go')
        ->assertSuccessful()
        ->expectsOutput('System runners job dispatched to queue.');

    \Illuminate\Support\Facades\Queue::assertPushed(\AdeildoJr\Runner\Jobs\RunRunnersJob::class);
});

it('runs synchronously with --sync flag', function () {
    $this->artisan('runner:go', ['--sync' => true])
        ->assertSuccessful()
        ->expectsOutput('Runners completed synchronously.');
});

it('dispatches specific runner when --runner flag is provided', function () {
    \Illuminate\Support\Facades\Queue::fake();

    $this->artisan('runner:go', [
        '--runner' => 'App\\Runners\\SendWelcomeEmails',
    ])
        ->assertSuccessful()
        ->expectsOutput('Running specific runner: App\\Runners\\SendWelcomeEmails')
        ->expectsOutput('System runners job dispatched to queue.');

    \Illuminate\Support\Facades\Queue::assertPushed(\AdeildoJr\Runner\Jobs\RunRunnersJob::class);
});
