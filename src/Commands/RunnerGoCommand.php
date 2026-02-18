<?php

namespace AdeildoJr\Runner\Commands;

use AdeildoJr\Runner\Jobs\RunRunnersJob;
use Illuminate\Console\Command;

class RunnerGoCommand extends Command
{
    protected $signature = 'runner:go
                            {--runner= : Run a specific runner by class name}
                            {--force : Force run even if runner has already completed}
                            {--sync : Run runners synchronously instead of queuing}';

    protected $description = 'Run system runners';

    public function handle(): int
    {
        $specificRunner = $this->option('runner');
        $sync = $this->option('sync');

        if ($specificRunner) {
            $this->info("Running specific runner: {$specificRunner}");
        } else {
            $this->info('Running all pending system runners...');
        }

        if ($sync) {
            $job = new RunRunnersJob($specificRunner);
            $job->handle();
            $this->info('Runners completed synchronously.');
        } else {
            RunRunnersJob::dispatch($specificRunner);
            $this->info('System runners job dispatched to queue.');
        }

        return self::SUCCESS;
    }
}
