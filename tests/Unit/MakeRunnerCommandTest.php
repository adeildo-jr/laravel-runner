<?php

use AdeildoJr\Runner\Commands\MakeRunnerCommand;
use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    $this->command = new MakeRunnerCommand(app(Filesystem::class));
});

it('generates correct runner name from class name', function () {
    $reflection = new ReflectionMethod($this->command, 'generateRunnerName');
    $reflection->setAccessible(true);

    expect($reflection->invoke($this->command, 'SendWelcomeEmails'))->toBe('send-welcome-emails');
    expect($reflection->invoke($this->command, 'CleanUpOldData'))->toBe('clean-up-old-data');
    expect($reflection->invoke($this->command, 'ProcessOrders'))->toBe('process-orders');
    // Runner suffix is removed from the end
    expect($reflection->invoke($this->command, 'ExampleRunner'))->toBe('example');
    expect($reflection->invoke($this->command, 'TestRunner'))->toBe('test');
});

it('has correct signature', function () {
    expect($this->command->getName())->toBe('make:runner');
});

it('has correct description', function () {
    expect($this->command->getDescription())->toBe('Create a new runner class');
});
