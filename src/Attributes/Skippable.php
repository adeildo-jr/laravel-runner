<?php

namespace AdeildoJr\Runner\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Skippable
{
    public function __construct(
        public bool $skippable = true
    ) {}
}
