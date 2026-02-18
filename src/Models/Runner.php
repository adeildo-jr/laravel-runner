<?php

namespace AdeildoJr\Runner\Models;

use AdeildoJr\Runner\Enums\RunnerStatus;
use Illuminate\Database\Eloquent\Model;

class Runner extends Model
{
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
        'status' => RunnerStatus::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function markAsRunning(): void
    {
        $this->update([
            'status' => RunnerStatus::Running,
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(?string $output = null): void
    {
        $this->update([
            'status' => RunnerStatus::Completed,
            'output' => $output,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => RunnerStatus::Failed,
            'error' => $error,
            'failed_at' => now(),
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === RunnerStatus::Pending;
    }

    public function isRunning(): bool
    {
        return $this->status === RunnerStatus::Running;
    }

    public function isCompleted(): bool
    {
        return $this->status === RunnerStatus::Completed;
    }

    public function isFailed(): bool
    {
        return $this->status === RunnerStatus::Failed;
    }
}
