<?php

use AdeildoJr\Runner\Jobs\RunRunnersJob;
use AdeildoJr\Runner\Tests\Models\Runner;

beforeEach(function () {
    // Configure model in config
    config()->set('runner.model', Runner::class);
    config()->set('runner.base_class', AdeildoJr\Runner\Runner::class);
});

it('processes pending tasks', function () {
    $taskContent = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Runner;

class TestRunnerTask extends Runner
{
    protected ?string $name = 'test-runner-task';
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        return 'Test task executed';
    }

    public function down(): void
    {
    }
}
PHP;

    $this->createTestTaskClass('TestRunnerTask', $taskContent);

    $job = new RunRunnersJob;
    $job->handle();

    $task = Runner::first();
    expect($task)->not->toBeNull();
    expect($task->runner_class)->toBe('Tests\\Temp\\Runners\\TestRunnerTask');
    expect($task->status)->toBe('completed');
    expect($task->output)->toBe('Test task executed');
    expect($task->completed_at)->not->toBeNull();
});

it('skips already completed tasks', function () {
    $taskContent = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Runner;

class CompletedTask extends Runner
{
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        return 'Should not run';
    }

    public function down(): void
    {
    }
}
PHP;

    $this->createTestTaskClass('CompletedTask', $taskContent);

    // Pre-create as completed
    Runner::create([
        'runner_class' => 'Tests\\Temp\\Runners\\CompletedTask',
        'status' => 'completed',
        'completed_at' => now(),
    ]);

    $job = new RunRunnersJob;
    $job->handle();

    // Should only have the one record we created
    expect(Runner::count())->toBe(1);
});

it('uses database transaction when configured', function () {
    $taskContent = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Runner;
use Illuminate\Support\Facades\DB;

class TransactionTask extends Runner
{
    protected bool $queue = false;
    protected bool $useTransaction = true;

    public static $executed = false;

    public function run(): ?string
    {
        self::$executed = true;
        return 'With transaction';
    }

    public function down(): void
    {
    }
}
PHP;

    $this->createTestTaskClass('TransactionTask', $taskContent);

    $job = new RunRunnersJob;
    $job->handle();

    $task = Runner::first();
    expect($task->status)->toBe('completed');
    expect($task->output)->toBe('With transaction');
});

it('handles task failures', function () {
    $taskContent = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Runner;

class FailingTask extends Runner
{
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        throw new \Exception('Task failed intentionally');
    }

    public function down(): void
    {
    }
}
PHP;

    $this->createTestTaskClass('FailingTask', $taskContent);

    $job = new RunRunnersJob;

    try {
        $job->handle();
    } catch (\Exception $e) {
        // Expected
    }

    $task = Runner::first();
    expect($task)->not->toBeNull();
    expect($task->status)->toBe('failed');
    expect($task->error)->toContain('Task failed intentionally');
});

it('processes only specific task when specified', function () {
    $task1Content = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Runner;

class TaskOne extends Runner
{
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        return 'Task One';
    }

    public function down(): void
    {
    }
}
PHP;

    $task2Content = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Runner;

class TaskTwo extends Runner
{
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        return 'Task Two';
    }

    public function down(): void
    {
    }
}
PHP;

    $this->createTestTaskClass('TaskOne', $task1Content);
    $this->createTestTaskClass('TaskTwo', $task2Content);

    $job = new RunRunnersJob('Tests\\Temp\\Runners\\TaskTwo');
    $job->handle();

    expect(Runner::count())->toBe(1);
    expect(Runner::first()->runner_class)->toBe('Tests\\Temp\\Runners\\TaskTwo');
});

it('skips abstract classes', function () {
    $abstractContent = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Runner;

abstract class AbstractTask extends Runner
{
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        return 'Abstract';
    }

    public function down(): void
    {
    }
}
PHP;

    $this->createTestTaskClass('AbstractTask', $abstractContent);

    $job = new RunRunnersJob;
    $job->handle();

    expect(Runner::count())->toBe(0);
});

it('updates task status to running before execution', function () {
    $taskContent = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Runner;

class StatusTask extends Runner
{
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        return 'Done';
    }

    public function down(): void
    {
    }
}
PHP;

    $this->createTestTaskClass('StatusTask', $taskContent);

    $job = new RunRunnersJob;
    $job->handle();

    $task = Runner::first();
    expect($task->started_at)->not->toBeNull();
    expect($task->status)->toBe('completed');
});
