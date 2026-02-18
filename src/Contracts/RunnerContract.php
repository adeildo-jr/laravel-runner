<?php

namespace AdeildoJr\Runner\Contracts;

interface RunnerContract
{
    public function up(): void;

    public function down(): void;

    public function getName(): string;

    public function shouldQueue(): bool;

    public function getQueue(): ?string;
}
