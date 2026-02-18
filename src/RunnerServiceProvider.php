<?php

namespace AdeildoJr\Runner;

use AdeildoJr\Runner\Commands\MakeRunnerCommand;
use AdeildoJr\Runner\Commands\RetryRunnersCommand;
use AdeildoJr\Runner\Commands\RunnerGoCommand;
use AdeildoJr\Runner\Jobs\RunRunnersJob;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class RunnerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('runner')
            ->hasConfigFile()
            ->hasMigration('create_runners_table')
            ->hasCommand(RunnerGoCommand::class)
            ->hasCommand(MakeRunnerCommand::class)
            ->hasCommand(RetryRunnersCommand::class);
    }

    public function packageBooted(): void
    {
        // Register the job in the container
        $this->app->bind(RunRunnersJob::class, function ($app) {
            return new RunRunnersJob;
        });
    }
}
