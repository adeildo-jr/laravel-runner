<?php

use AdeildoJr\Runner\Attributes\ShouldQueue;
use AdeildoJr\Runner\Attributes\RunnerName;
use AdeildoJr\Runner\Attributes\RunnerQueue;
use AdeildoJr\Runner\Attributes\UseTransaction;
use AdeildoJr\Runner\Runner;

it('attributes work with anonymous classes', function () {
    $task = new #[RunnerName('anonymous-task')] #[ShouldQueue(false)] #[RunnerQueue('custom')] #[UseTransaction(false)] class extends Runner
    {
        public function run(): ?string
        {
            return 'Anonymous task executed';
        }

        public function down(): void {}
    };

    expect($task->getName())->toBe('anonymous-task');
    expect($task->shouldQueue())->toBeFalse();
    expect($task->getQueue())->toBe('custom');
    expect($task->useTransaction())->toBeFalse();
});

it('anonymous classes can use partial attributes', function () {
    $task = new #[RunnerName('partial-anon')] class extends Runner
    {
        protected bool $queue = false;

        protected bool $useTransaction = true;

        public function run(): ?string
        {
            return null;
        }

        public function down(): void {}
    };

    // Name from attribute
    expect($task->getName())->toBe('partial-anon');
    // Queue from property (no attribute)
    expect($task->shouldQueue())->toBeFalse();
    // Transaction from property (no attribute)
    expect($task->useTransaction())->toBeTrue();
});

it('anonymous classes have non-deterministic class names', function () {
    $task1 = new class extends Runner
    {
        public function run(): ?string
        {
            return null;
        }

        public function down(): void {}
    };

    $task2 = new class extends Runner
    {
        public function run(): ?string
        {
            return null;
        }

        public function down(): void {}
    };

    $name1 = get_class($task1);
    $name2 = get_class($task2);

    // Anonymous classes have different names
    expect($name1)->not->toBe($name2);

    // Both contain file path and line number
    expect($name1)->toContain('AnonymousClassesTest');
    expect($name2)->toContain('AnonymousClassesTest');
});

it('anonymous classes are tracked differently each time', function () {
    // This test demonstrates why anonymous classes aren't ideal for system tasks
    // Each instance gets a different class name, so they would be tracked separately

    $task1 = new #[RunnerName('tracked-task')] class extends Runner
    {
        public function run(): ?string
        {
            return 'first';
        }

        public function down(): void {}
    };

    $task2 = new #[RunnerName('tracked-task')] class extends Runner
    {
        public function run(): ?string
        {
            return 'second';
        }

        public function down(): void {}
    };

    // Same task name from attribute
    expect($task1->getName())->toBe('tracked-task');
    expect($task2->getName())->toBe('tracked-task');

    // But different class names
    expect(get_class($task1))->not->toBe(get_class($task2));
});
