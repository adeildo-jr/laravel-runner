<?php

namespace AdeildoJr\Runner\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class UseTransaction
{
    public function __construct(
        public bool $useTransaction = true
    ) {}
}
