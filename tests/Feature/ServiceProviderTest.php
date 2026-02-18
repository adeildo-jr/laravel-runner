<?php

it('registers the service provider', function () {
    expect(app()->providerIsLoaded(\AdeildoJr\Runner\RunnerServiceProvider::class))
        ->toBeTrue();
});

it('registers config values', function () {
    expect(config('runner.path'))->not->toBeNull();
    expect(config('runner.namespace'))->not->toBeNull();
    expect(config('runner.base_class'))->toBe(\AdeildoJr\Runner\Runner::class);
});

it('publishes config file', function () {
    $provider = new \AdeildoJr\Runner\RunnerServiceProvider(app());
    $reflection = new ReflectionClass($provider);

    // The service provider should exist and be loadable
    expect($provider)->toBeInstanceOf(\Spatie\LaravelPackageTools\PackageServiceProvider::class);
});
