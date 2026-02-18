<?php

namespace AdeildoJr\Runner\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ShouldQueue
{
    public function __construct(
        public bool $queue = true
    ) {}
}
