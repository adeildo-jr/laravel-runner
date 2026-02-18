<?php

use AdeildoJr\Runner\Attributes\RunnerName;
use AdeildoJr\Runner\Attributes\RunnerQueue;
use AdeildoJr\Runner\Attributes\ShouldQueue;
use AdeildoJr\Runner\Attributes\UseTransaction;
use AdeildoJr\Runner\Runner;

#[RunnerName('custom-attribute-name')]
#[ShouldQueue(false)]
#[RunnerQueue('high-priority')]
#[UseTransaction(false)]
class RunnerWithAttributes extends Runner
{
    protected ?string $name = 'property-name';

    protected bool $queue = true;

    protected ?string $queueName = 'default';

    protected bool $useTransaction = true;

    public function run(): ?string
    {
        return 'Runner executed';
    }

    public function down(): void {}
}

class RunnerWithProperties extends Runner
{
    protected ?string $name = 'property-runner';

    protected bool $queue = false;

    protected ?string $queueName = 'low';

    protected bool $useTransaction = false;

    public function run(): ?string
    {
        return 'Runner executed';
    }

    public function down(): void {}
}

#[RunnerName('mixed-name')]
class RunnerWithMixedConfig extends Runner
{
    protected bool $queue = false;

    protected ?string $queueName = 'mixed-queue';

    public function run(): ?string
    {
        return 'Runner executed';
    }

    public function down(): void {}
}

it('reads runner name from attribute when present', function () {
    $runner = new RunnerWithAttributes;
    expect($runner->getName())->toBe('custom-attribute-name');
});

it('reads runner name from property when no attribute', function () {
    $runner = new RunnerWithProperties;
    expect($runner->getName())->toBe('property-runner');
});

it('uses class name when neither attribute nor property is set', function () {
    $runner = new class extends Runner
    {
        public function run(): ?string
        {
            return null;
        }

        public function down(): void {}
    };

    expect($runner->getName())->toBeString();
    expect($runner->getName())->not->toBeEmpty();
});

it('reads queue setting from attribute when present', function () {
    $runner = new RunnerWithAttributes;
    expect($runner->shouldQueue())->toBeFalse();
});

it('reads queue setting from property when no attribute', function () {
    $runner = new RunnerWithProperties;
    expect($runner->shouldQueue())->toBeFalse();
});

it('defaults to true for queue when neither attribute nor property is set', function () {
    $runner = new RunnerWithMixedConfig;
    expect($runner->shouldQueue())->toBeFalse();
});

it('reads queue name from attribute when present', function () {
    $runner = new RunnerWithAttributes;
    expect($runner->getQueue())->toBe('high-priority');
});

it('reads queue name from property when no attribute', function () {
    $runner = new RunnerWithProperties;
    expect($runner->getQueue())->toBe('low');
});

it('reads transaction setting from attribute when present', function () {
    $runner = new RunnerWithAttributes;
    expect($runner->useTransaction())->toBeFalse();
});

it('reads transaction setting from property when no attribute', function () {
    $runner = new RunnerWithProperties;
    expect($runner->useTransaction())->toBeFalse();
});

it('allows partial attribute configuration', function () {
    $runner = new RunnerWithMixedConfig;

    expect($runner->getName())->toBe('mixed-name');
    expect($runner->shouldQueue())->toBeFalse();
    expect($runner->getQueue())->toBe('mixed-queue');
    expect($runner->useTransaction())->toBeTrue();
});
