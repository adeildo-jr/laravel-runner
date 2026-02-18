<?php

namespace AdeildoJr\Runner\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class RunnerName
{
    public function __construct(
        public string $name
    ) {}
}
