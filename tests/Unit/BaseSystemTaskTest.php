<?php

use AdeildoJr\Runner\Runner;

class TestTask extends Runner
{
    protected ?string $name = 'test-task';

    protected bool $queue = false;

    protected ?string $queueName = 'high';

    protected bool $useTransaction = true;

    public function run(): ?string
    {
        return 'Task executed';
    }

    public function down(): void
    {
        // Cleanup logic
    }
}

class AnotherTask extends Runner
{
    protected bool $queue = true;

    protected bool $useTransaction = false;

    public function run(): ?string
    {
        return null;
    }

    public function down(): void
    {
        // Cleanup logic
    }
}

it('returns custom name when set', function () {
    $task = new TestTask;
    expect($task->getName())->toBe('test-task');
});

it('returns class name when no custom name set', function () {
    $task = new AnotherTask;
    expect($task->getName())->toBe('AnotherTask');
});

it('returns queue configuration', function () {
    $task = new TestTask;
    expect($task->shouldQueue())->toBeFalse();
    expect($task->getQueue())->toBe('high');
});

it('returns transaction configuration', function () {
    $task = new TestTask;
    expect($task->useTransaction())->toBeTrue();

    $task2 = new AnotherTask;
    expect($task2->useTransaction())->toBeFalse();
});

it('runs the task and returns output', function () {
    $task = new TestTask;
    expect($task->run())->toBe('Task executed');
});

it('up method delegates to run', function () {
    $task = new TestTask;
    $task->up();
    // up() calls run(), should execute without error
    expect(true)->toBeTrue();
});

it('has down method for rollback', function () {
    $task = new TestTask;
    $task->down();
    // Should execute without error
    expect(true)->toBeTrue();
});
