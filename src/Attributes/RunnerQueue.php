<?php

namespace AdeildoJr\Runner\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class RunnerQueue
{
    public function __construct(
        public ?string $queue = null
    ) {}
}
