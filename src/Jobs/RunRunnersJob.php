<?php

namespace AdeildoJr\Runner\Jobs;

use AdeildoJr\Runner\Events\RunnerFailed;
use AdeildoJr\Runner\Events\RunnerFinished;
use AdeildoJr\Runner\Events\RunnerStarted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Throwable;

class RunRunnersJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 2400;

    /**
     * Specific runner class to run (optional).
     */
    protected ?string $specificRunner = null;

    /**
     * Whether to only retry failed runners.
     */
    protected bool $retryFailed = false;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $specificRunner = null, bool $retryFailed = false)
    {
        $this->specificRunner = $specificRunner;
        $this->retryFailed = $retryFailed;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $directory = config('runner.path', app_path('Runners'));
        $baseClass = config('runner.base_class', 'AdeildoJr\\Runner\\Runner');
        $runnerModel = config('runner.model', 'App\\Models\\Runner');

        if (! File::exists($directory)) {
            return;
        }

        $files = collect(File::files($directory))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->sortBy(fn ($file) => $file->getFilename())
            ->values();

        foreach ($files as $file) {
            $class = $this->getClassFromFile($file->getPathname());

            if ($class === null) {
                continue;
            }

            // Skip the base class if present.
            if ($class === $baseClass) {
                continue;
            }

            // If specific runner requested, skip others.
            if ($this->specificRunner !== null && $class !== $this->specificRunner) {
                continue;
            }

            if (! class_exists($class)) {
                require_once $file->getPathname();
            }

            if (! class_exists($class)) {
                continue;
            }

            // Only process concrete subclasses of base class
            if (! is_subclass_of($class, $baseClass)) {
                continue;
            }

            $reflection = new \ReflectionClass($class);
            if ($reflection->isAbstract()) {
                continue;
            }

            // Check if runner should be skipped
            $instance = app($class);
            if (method_exists($instance, 'isSkippable') && $instance->isSkippable() === true) {
                continue;
            }

            $this->processRunner($class, $runnerModel);
        }
    }

    /**
     * Process a single runner.
     *
     * @param  class-string  $class
     * @param  class-string<Model>  $runnerModel
     */
    protected function processRunner(string $class, string $runnerModel): void
    {
        /** @var Model $runner */
        $runner = $runnerModel::firstOrCreate([
            'runner_class' => $class,
        ], [
            'status' => 'pending',
        ]);

        // If retrying failed, only process failed runners
        if ($this->retryFailed && $runner->status !== 'failed') {
            return;
        }

        // If not retrying and already completed, skip
        if (! $this->retryFailed && $runner->status === 'completed') {
            return;
        }

        $runner->update([
            'status' => 'running',
            'started_at' => now(),
            'error' => null,
            'failed_at' => null,
        ]);

        $instance = app($class);

        // Dispatch started event
        Event::dispatch(new RunnerStarted($instance, $runner));

        $executeRunner = function () use ($runner, $instance): void {
            $output = $instance->run();

            $runner->update([
                'status' => 'completed',
                'output' => $output,
                'completed_at' => now(),
                'failed_at' => null,
            ]);

            // Dispatch finished event
            Event::dispatch(new RunnerFinished($instance, $runner, $output));
        };

        try {
            if (method_exists($instance, 'useTransaction') && $instance->useTransaction() === true) {
                DB::transaction($executeRunner(...));
            } else {
                $executeRunner();
            }
        } catch (Throwable $e) {
            $runner->update([
                'status' => 'failed',
                'error' => $e->getMessage()."\n".$e->getTraceAsString(),
                'failed_at' => now(),
            ]);

            // Dispatch failed event
            Event::dispatch(new RunnerFailed($instance, $runner, $e));

            throw $e;
        }
    }

    /**
     * Extract the fully-qualified class name from a file.
     *
     * @return class-string|null
     */
    protected function getClassFromFile(string $path): ?string
    {
        $contents = File::get($path);

        // Extract namespace
        $namespace = null;
        if (preg_match('/namespace\s+([^;]+);/', $contents, $matches)) {
            $namespace = $matches[1];
        }

        // Extract class name
        $class = null;
        if (preg_match('/class\s+(\w+)/', $contents, $matches)) {
            $class = $matches[1];
        }

        if ($namespace === null || $class === null) {
            return null;
        }

        /** @var class-string */
        return $namespace.'\\'.$class;
    }
}
