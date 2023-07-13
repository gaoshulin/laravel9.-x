<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeServiceCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service';

    protected $type = 'Service';

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        return $this->replaceRepository(parent::buildClass($name));
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub() {
        // Implement getStub() method.
        return $this->laravel->basePath('/stubs/service/service.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Services';
    }

    /**
     * Replace the entity name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceRepository($stub)
    {
        $name = $this->getNameInput();
        $entity = str_replace('Service', '', Str::afterLast($name, '/'));

        $name = ltrim($name, '\\/');
        $name = str_replace('/', '\\', $name);
        $module = str_replace('Service', '', $name);

        $stub = str_replace(['DummyEntity', '{{ entity }}', '{{entity}}'], $entity, $stub);
        $stub = str_replace(['DummyModule', '{{ module }}', '{{module}}'], $module, $stub);

        return $stub;
    }
}
