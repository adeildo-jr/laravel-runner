<?php

return [
    'path' => env('RUNNERS_PATH', app_path('Runners')),

    'namespace' => env('RUNNERS_NAMESPACE', 'App\\Runners'),

    'base_class' => AdeildoJr\Runner\Runner::class,

    'model' => AdeildoJr\Runner\Models\Runner::class,

    'table' => env('RUNNER_TABLE', 'runners'),
];
