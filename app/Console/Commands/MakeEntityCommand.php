<?php

namespace App\Console\Commands;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Prettus\Repository\Generators\Commands\EntityCommand as Command;

/**
 * Class EntityCommand
 *
 * @package Prettus\Repository\Generators\Commands
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class MakeEntityCommand extends Command
{
    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'make:entity';

    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Create a new entity.';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function fire()
    {

        // 不需要
        // if ($this->confirm('Would you like to create a Presenter? [y|N]')) {
        //     $this->call('make:presenter', [
        //         'name'    => $this->argument('name'),
        //         '--force' => $this->option('force'),
        //     ]);
        // }

        $validator = $this->option('validator');
        if (is_null($validator) && $this->confirm('Would you like to create a Validator? [y|N]')) {
            $validator = 'yes';
        }

        if ($validator == 'yes') {
            $this->call('make:validator', [
                'name'    => $this->argument('name'),
                '--rules' => $this->option('rules'),
                '--force' => $this->option('force'),
            ]);
        }

        if ($this->confirm('Would you like to create a Controller? [y|N]')) {
            $resource_args = [
                'name'    => $this->argument('name')
            ];

            // Generate a controller
            $controller_command = ((float) app()->version() >= 5.5  ? 'make:rest-controller' : 'make:resource');
            $this->call($controller_command, $resource_args);

            // Generate a controller resource
            $this->call('make:resource', [
                'name'         => 'Api/' . $this->argument('name') . 'Resource',
            ]);
            // Generate a collection resource
            if ($this->option('collection')) {
                $this->call('make:resource', [
                    'name' => 'Api/' . $this->argument('name') . 'Collection',
                ]);
            }
        }

        if ($this->confirm('Would you like to create a Service? [y|N]')) {
            // Generate a service
            $this->call('make:service', [
                'name' => $this->argument('name') . 'Service',
            ]);
        }

        // Generate a repository
        $this->call('make:repository', [
            'name'             => $this->argument('name'),
            '--fillable'        => $this->option('fillable'),
            '--rules'          => $this->option('rules'),
            '--validator'      => $validator,
            '--force'          => $this->option('force'),
            // 默认不生成迁移脚本
            '--skip-migration' => InputOption::VALUE_NONE,
        ]);

        $this->call('make:bindings', [
            'name'    => $this->argument('name'),
            '--force' => $this->option('force')
        ]);
    }


    /**
     * The array of command options.
     *
     * @return array
     */
    public function getOptions()
    {
        $options = parent::getOptions();
        $options[] = ['collection', 'c', InputOption::VALUE_OPTIONAL, 'Create a resource collection', null];
        return $options;
    }
}
