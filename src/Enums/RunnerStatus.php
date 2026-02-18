<?php

namespace AdeildoJr\Runner\Enums;

enum RunnerStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
}
