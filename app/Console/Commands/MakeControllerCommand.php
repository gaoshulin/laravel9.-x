<?php

namespace App\Console\Commands;

use Prettus\Repository\Generators\Commands\ControllerCommand as Command;
use App\Support\ControllerGenerator;
use Prettus\Repository\Generators\FileAlreadyExistsException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class MakeControllerCommand
 * @package Prettus\Repository\Generators\Commands
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class MakeControllerCommand extends Command
{

    /**
     * The name of command.
     *
     * @var string
     */
    protected $name = 'make:resource';

    /**
     * The description of command.
     *
     * @var string
     */
    protected $description = 'Create a new RESTful controller.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Execute the command.
     */
    public function fire()
    {
        try {
            // 不需要
            // Generate create request for controller
            // $this->call('make:request', [
            //     'name' => $this->argument('name') . 'CreateRequest'
            // ]);

            // Generate update request for controller
            // $this->call('make:request', [
            //     'name' => $this->argument('name') . 'UpdateRequest'
            // ]);

            (new ControllerGenerator([
                'name' => $this->argument('name'),
                'force' => $this->option('force'),
            ]))->run();

            $this->info($this->type . ' created successfully.');
        } catch (FileAlreadyExistsException $e) {
            $this->error($this->type . ' already exists!');

            return false;
        }
    }
}
