<?php

namespace AdeildoJr\Runner\Events;

use AdeildoJr\Runner\Runner;
use Illuminate\Database\Eloquent\Model;

class RunnerFinished
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
     * The output from the runner.
     */
    public ?string $output;

    /**
     * Create a new event instance.
     */
    public function __construct(Runner $runner, Model $model, ?string $output = null)
    {
        $this->runner = $runner;
        $this->model = $model;
        $this->output = $output;
    }
}
