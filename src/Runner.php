<?php

namespace AdeildoJr\Runner;

use AdeildoJr\Runner\Attributes\RunnerName;
use AdeildoJr\Runner\Attributes\RunnerQueue;
use AdeildoJr\Runner\Attributes\ShouldQueue as ShouldQueueAttribute;
use AdeildoJr\Runner\Attributes\Skippable;
use AdeildoJr\Runner\Attributes\UseTransaction;
use AdeildoJr\Runner\Contracts\RunnerContract;
use ReflectionClass;

abstract class Runner implements RunnerContract
{
    protected ?string $name = null;

    protected bool $queue = true;

    protected ?string $queueName = null;

    protected bool $useTransaction = true;

    protected bool $skippable = false;

    private ?ReflectionClass $reflection = null;

    public function getName(): string
    {
        $reflection = $this->getReflection();
        $attributes = $reflection->getAttributes(RunnerName::class);

        if (! empty($attributes)) {
            return $attributes[0]->newInstance()->name;
        }

        return $this->name ?? class_basename(static::class);
    }

    public function shouldQueue(): bool
    {
        $reflection = $this->getReflection();
        $attributes = $reflection->getAttributes(ShouldQueueAttribute::class);

        if (! empty($attributes)) {
            return $attributes[0]->newInstance()->queue;
        }

        return $this->queue;
    }

    public function getQueue(): ?string
    {
        $reflection = $this->getReflection();
        $attributes = $reflection->getAttributes(RunnerQueue::class);

        if (! empty($attributes)) {
            return $attributes[0]->newInstance()->queue;
        }

        return $this->queueName;
    }

    public function useTransaction(): bool
    {
        $reflection = $this->getReflection();
        $attributes = $reflection->getAttributes(UseTransaction::class);

        if (! empty($attributes)) {
            return $attributes[0]->newInstance()->useTransaction;
        }

        return $this->useTransaction;
    }

    public function isSkippable(): bool
    {
        $reflection = $this->getReflection();
        $attributes = $reflection->getAttributes(Skippable::class);

        if (! empty($attributes)) {
            return $attributes[0]->newInstance()->skippable;
        }

        return $this->skippable;
    }

    private function getReflection(): ReflectionClass
    {
        if ($this->reflection === null) {
            $this->reflection = new ReflectionClass($this);
        }

        return $this->reflection;
    }

    abstract public function run(): ?string;

    abstract public function down(): void;

    public function up(): void
    {
        $this->run();
    }
}
