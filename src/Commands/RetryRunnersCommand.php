<?php

namespace AdeildoJr\Runner\Commands;

use AdeildoJr\Runner\Jobs\RunRunnersJob;
use Illuminate\Console\Command;

class RetryRunnersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'runner:retry
                            {--runner= : Retry a specific runner by class name}
                            {--sync : Retry runners synchronously instead of queuing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry failed runners';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $specificRunner = $this->option('runner');
        $sync = $this->option('sync');

        if ($specificRunner) {
            $this->info("Retrying specific failed runner: {$specificRunner}");
        } else {
            $this->info('Retrying all failed runners...');
        }

        if ($sync) {
            // Run synchronously
            $job = new RunRunnersJob($specificRunner, true);
            $job->handle();
            $this->info('Failed runners retried synchronously.');
        } else {
            // Dispatch to queue
            RunRunnersJob::dispatch($specificRunner, true);
            $this->info('Retry job dispatched to queue.');
        }

        return self::SUCCESS;
    }
}
