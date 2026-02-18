<?php

namespace AdeildoJr\Runner\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

class MakeRunnerCommand extends GeneratorCommand
{
    protected $name = 'make:runner';

    protected $description = 'Create a new runner class';

    protected $type = 'Runner';

    protected function getStub(): string
    {
        return __DIR__.'/../../stubs/Runner.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Runners';
    }

    protected function replaceClass($stub, $name): string
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        $stub = str_replace('{{ class }}', $class, $stub);
        $stub = str_replace('{{class}}', $class, $stub);

        $runnerName = $this->generateRunnerName($class);
        $stub = str_replace('{{ runnerName }}', $runnerName, $stub);
        $stub = str_replace('{{runnerName}}', $runnerName, $stub);

        return $stub;
    }

    protected function generateRunnerName(string $class): string
    {
        $name = preg_replace('/(?<!^)[A-Z]/', '-$0', $class);
        $name = strtolower($name);

        $name = preg_replace('/-runner$/', '', $name);

        return $name;
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the runner class'],
        ];
    }
}
