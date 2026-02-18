<?php

use AdeildoJr\Runner\Attributes\Skippable;
use AdeildoJr\Runner\Runner as BaseRunner;
use AdeildoJr\Runner\Tests\Models\Runner;

beforeEach(function () {
    config()->set('runner.model', Runner::class);
    config()->set('runner.base_class', BaseRunner::class);
});

// Task with skippable property set to true
class SkippablePropertyTask extends BaseRunner
{
    protected bool $skippable = true;

    protected bool $queue = false;

    protected bool $useTransaction = false;

    public static bool $wasRun = false;

    public function run(): ?string
    {
        self::$wasRun = true;

        return 'Should not run';
    }

    public function down(): void {}
}

// Task with skippable property set to false
class NonSkippablePropertyTask extends BaseRunner
{
    protected bool $skippable = false;

    protected bool $queue = false;

    protected bool $useTransaction = false;

    public static bool $wasRun = false;

    public function run(): ?string
    {
        self::$wasRun = true;

        return 'Should run';
    }

    public function down(): void {}
}

// Task with skippable attribute
#[Skippable(true)]
class SkippableAttributeTask extends BaseRunner
{
    protected bool $queue = false;

    protected bool $useTransaction = false;

    public static bool $wasRun = false;

    public function run(): ?string
    {
        self::$wasRun = true;

        return 'Should not run';
    }

    public function down(): void {}
}

// Task with skippable attribute set to false
#[Skippable(false)]
class NonSkippableAttributeTask extends BaseRunner
{
    protected bool $skippable = true; // Property would say true, but attribute overrides

    protected bool $queue = false;

    protected bool $useTransaction = false;

    public static bool $wasRun = false;

    public function run(): ?string
    {
        self::$wasRun = true;

        return 'Should run';
    }

    public function down(): void {}
}

// Task with default skippable (false)
class DefaultSkippableTask extends BaseRunner
{
    protected bool $queue = false;

    protected bool $useTransaction = false;

    public static bool $wasRun = false;

    public function run(): ?string
    {
        self::$wasRun = true;

        return 'Should run';
    }

    public function down(): void {}
}

it('skips tasks with skippable property set to true', function () {
    $taskContent = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Runner;

class SkippableTask extends Runner
{
    protected bool $skippable = true;
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        return 'Should not run';
    }

    public function down(): void {}
}
PHP;

    $this->createTestTaskClass('SkippableTask', $taskContent);

    $job = new \AdeildoJr\Runner\Jobs\RunRunnersJob;
    $job->handle();

    // Task should not be in database because it was skipped
    expect(Runner::count())->toBe(0);
});

it('runs tasks with skippable property set to false', function () {
    $taskContent = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Runner;

class NonSkippableTask extends Runner
{
    protected bool $skippable = false;
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        return 'Should run';
    }

    public function down(): void {}
}
PHP;

    $this->createTestTaskClass('NonSkippableTask', $taskContent);

    $job = new \AdeildoJr\Runner\Jobs\RunRunnersJob;
    $job->handle();

    // Task should be in database and completed
    expect(Runner::count())->toBe(1);
    expect(Runner::first()->status)->toBe('completed');
});

it('skips tasks with skippable attribute', function () {
    $taskContent = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Attributes\Skippable;
use AdeildoJr\Runner\Runner;

#[Skippable]
class SkippableAttrTask extends Runner
{
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        return 'Should not run';
    }

    public function down(): void {}
}
PHP;

    $this->createTestTaskClass('SkippableAttrTask', $taskContent);

    $job = new \AdeildoJr\Runner\Jobs\RunRunnersJob;
    $job->handle();

    // Task should not be in database because it was skipped
    expect(Runner::count())->toBe(0);
});

it('attribute overrides property for skippable', function () {
    $taskContent = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Attributes\Skippable;
use AdeildoJr\Runner\Runner;

#[Skippable(false)]
class OverrideTask extends Runner
{
    protected bool $skippable = true; // Property says true, attribute says false
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        return 'Should run';
    }

    public function down(): void {}
}
PHP;

    $this->createTestTaskClass('OverrideTask', $taskContent);

    $job = new \AdeildoJr\Runner\Jobs\RunRunnersJob;
    $job->handle();

    // Task should run because attribute overrides property
    expect(Runner::count())->toBe(1);
    expect(Runner::first()->status)->toBe('completed');
});

it('isSkippable returns correct value from property', function () {
    $skippableTask = new SkippablePropertyTask;
    expect($skippableTask->isSkippable())->toBeTrue();

    $nonSkippableTask = new NonSkippablePropertyTask;
    expect($nonSkippableTask->isSkippable())->toBeFalse();

    $defaultTask = new DefaultSkippableTask;
    expect($defaultTask->isSkippable())->toBeFalse();
});

it('isSkippable returns correct value from attribute', function () {
    $skippableTask = new SkippableAttributeTask;
    expect($skippableTask->isSkippable())->toBeTrue();

    $nonSkippableTask = new NonSkippableAttributeTask;
    expect($nonSkippableTask->isSkippable())->toBeFalse();
});
