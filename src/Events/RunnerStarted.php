<?php

namespace AdeildoJr\Runner\Events;

use AdeildoJr\Runner\Runner;
use Illuminate\Database\Eloquent\Model;

class RunnerStarted
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
     * Create a new event instance.
     */
    public function __construct(Runner $runner, Model $model)
    {
        $this->runner = $runner;
        $this->model = $model;
    }
}
