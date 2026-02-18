<?php

namespace AdeildoJr\Runner\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Runner extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'runners';

    protected $fillable = [
        'runner_class',
        'status',
        'output',
        'error',
        'started_at',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];
}
