<?php

namespace AdeildoJr\Runner\Events;

use AdeildoJr\Runner\Runner;
use Illuminate\Database\Eloquent\Model;

readonly class RunnerStarted
{
    /**
     * Create a new event instance.
     */
    public function __construct(public Runner $runner, public Model $model)
    {
        //
    }
}
