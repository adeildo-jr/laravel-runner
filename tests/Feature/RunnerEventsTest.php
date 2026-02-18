<?php

use AdeildoJr\Runner\Events\RunnerFailed;
use AdeildoJr\Runner\Events\RunnerFinished;
use AdeildoJr\Runner\Events\RunnerStarted;
use AdeildoJr\Runner\Jobs\RunRunnersJob;
use AdeildoJr\Runner\Tests\Models\Runner;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    config()->set('runner.model', Runner::class);
    config()->set('runner.base_class', AdeildoJr\Runner\Runner::class);
});

it('dispatches runner started event', function () {
    Event::fake([RunnerStarted::class]);

    $taskContent = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Runner;

class EventTestRunner extends Runner
{
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        return 'Success';
    }

    public function down(): void {}
}
PHP;

    $this->createTestTaskClass('EventTestRunner', $taskContent);

    $job = new RunRunnersJob;
    $job->handle();

    Event::assertDispatched(RunnerStarted::class, function ($event) {
        return $event->runner instanceof \Tests\Temp\Runners\EventTestRunner
            && $event->model instanceof Runner;
    });
});

it('dispatches runner finished event', function () {
    Event::fake([RunnerFinished::class]);

    $taskContent = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Runner;

class EventFinishedRunner extends Runner
{
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        return 'Completed successfully';
    }

    public function down(): void {}
}
PHP;

    $this->createTestTaskClass('EventFinishedRunner', $taskContent);

    $job = new RunRunnersJob;
    $job->handle();

    Event::assertDispatched(RunnerFinished::class, function ($event) {
        return $event->runner instanceof \Tests\Temp\Runners\EventFinishedRunner
            && $event->model instanceof Runner
            && $event->output === 'Completed successfully';
    });
});

it('dispatches runner failed event', function () {
    Event::fake([RunnerFailed::class]);

    $taskContent = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Runner;

class EventFailedRunner extends Runner
{
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        throw new \Exception('Intentional failure');
    }

    public function down(): void {}
}
PHP;

    $this->createTestTaskClass('EventFailedRunner', $taskContent);

    $job = new RunRunnersJob;

    try {
        $job->handle();
    } catch (\Exception $e) {
        // Expected
    }

    Event::assertDispatched(RunnerFailed::class, function ($event) {
        return $event->runner instanceof \Tests\Temp\Runners\EventFailedRunner
            && $event->model instanceof Runner
            && $event->exception->getMessage() === 'Intentional failure';
    });
});

it('dispatches both started and finished events for successful runner', function () {
    Event::fake([RunnerStarted::class, RunnerFinished::class]);

    $taskContent = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Runner;

class BothEventsRunner extends Runner
{
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        return 'Done';
    }

    public function down(): void {}
}
PHP;

    $this->createTestTaskClass('BothEventsRunner', $taskContent);

    $job = new RunRunnersJob;
    $job->handle();

    Event::assertDispatched(RunnerStarted::class);
    Event::assertDispatched(RunnerFinished::class);
});

it('does not dispatch finished event when runner fails', function () {
    Event::fake([RunnerFinished::class, RunnerFailed::class]);

    $taskContent = <<<'PHP'
<?php

namespace Tests\Temp\Runners;

use AdeildoJr\Runner\Runner;

class NoFinishEventRunner extends Runner
{
    protected bool $queue = false;
    protected bool $useTransaction = false;

    public function run(): ?string
    {
        throw new \Exception('Failed');
    }

    public function down(): void {}
}
PHP;

    $this->createTestTaskClass('NoFinishEventRunner', $taskContent);

    $job = new RunRunnersJob;

    try {
        $job->handle();
    } catch (\Exception $e) {
        // Expected
    }

    Event::assertNotDispatched(RunnerFinished::class);
    Event::assertDispatched(RunnerFailed::class);
});
