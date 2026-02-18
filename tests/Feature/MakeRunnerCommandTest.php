<?php

use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    $this->fs = new Filesystem;
});

afterEach(function () {
    $paths = [
        app_path('Runners/TestRunner.php'),
        app_path('Runners/SendWelcomeEmails.php'),
    ];

    foreach ($paths as $path) {
        if ($this->fs->exists($path)) {
            $this->fs->delete($path);
        }
    }
});

it('creates a new runner file', function () {
    $this->artisan('make:runner', ['name' => 'TestRunner'])
        ->assertSuccessful();

    $path = app_path('Runners/TestRunner.php');
    expect($this->fs->exists($path))->toBeTrue();

    $content = $this->fs->get($path);
    expect($content)->toContain('class TestRunner extends Runner');
    expect($content)->toContain("protected ?string \$name = 'test'");
});

it('generates correct runner name in file', function () {
    $this->artisan('make:runner', ['name' => 'SendWelcomeEmails'])
        ->assertSuccessful();

    $path = app_path('Runners/SendWelcomeEmails.php');
    $content = $this->fs->get($path);
    expect($content)->toContain("protected ?string \$name = 'send-welcome-emails'");
});

it('creates runner with proper namespace', function () {
    $this->artisan('make:runner', ['name' => 'TestRunner'])
        ->assertSuccessful();

    $path = app_path('Runners/TestRunner.php');
    $content = $this->fs->get($path);
    expect($content)->toContain('namespace App\\Runners;');
});

it('requires a name argument', function () {
    $this->artisan('make:runner')
        ->expectsQuestion('What should the runner be named?', 'TestRunner')
        ->assertSuccessful();
});
