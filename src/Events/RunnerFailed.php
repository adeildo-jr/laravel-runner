<?php

namespace AdeildoJr\Runner\Events;

use AdeildoJr\Runner\Runner;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class RunnerFailed
{
    /**
     * The runner instance.
     */
    public Runner $runner;

    /**
     * The runner database model.
     */
    public Model $model;

    /**
     * The exception that caused the failure.
     */
    public Throwable $exception;

    /**
     * Create a new event instance.
     */
    public function __construct(Runner $runner, Model $model, Throwable $exception)
    {
        $this->runner = $runner;
        $this->model = $model;
        $this->exception = $exception;
    }
}
