<?php

namespace AdeildoJr\Runner\Tests;

use AdeildoJr\Runner\RunnerServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'AdeildoJr\\Runner\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        // Clean up test tasks directory before each test
        $this->cleanTestTasksDirectory();
    }

    protected function tearDown(): void
    {
        // Clean up test tasks directory after each test
        $this->cleanTestTasksDirectory();

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            RunnerServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        // Set config before running migrations
        config()->set('runner.table', 'runners');

        // Run migrations
        $migration = include __DIR__.'/../database/migrations/create_runners_table.php.stub';
        $migration->up();

        // Set tasks path for testing
        config()->set('runner.path', __DIR__.'/temp/Runners');
        config()->set('runner.namespace', 'Tests\\Temp\\Runners');
        config()->set('runner.model', \AdeildoJr\Runner\Tests\Models\Runner::class);
    }

    protected function cleanTestTasksDirectory(): void
    {
        $path = __DIR__.'/temp';
        if (is_dir($path)) {
            $files = new Filesystem;
            $files->deleteDirectory($path);
        }
    }

    protected function createTestTaskClass(string $name, string $content): string
    {
        $path = __DIR__.'/temp/Runners';

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $filePath = $path.'/'.$name.'.php';
        file_put_contents($filePath, $content);

        return $filePath;
    }
}
