<?php

namespace App\Support;

use Illuminate\Support\Str;
use Prettus\Repository\Generators\ControllerGenerator as Generator;

/**
 * Class ControllerGenerator
 * @package Prettus\Repository\Generators
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class ControllerGenerator extends Generator
{
    /**
     * Get stub name.
     *
     * @var string
     */
    protected $stub = 'controller/controller';

    /**
     * Get destination path for generated file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getBasePath() . '/' . parent::getConfigGeneratorClassPath($this->getPathConfigNode(), true) . '/' . $this->getModule() . '/' . $this->getControllerName() . 'Controller.php';
    }

    /**
     * Gets module name via input
     *
     * @return string
     */
    public function getModule()
    {
        return Str::beforeLast($this->getName(), '/');
    }

    /**
     * Get array replacements.
     *
     * @return array
     */
    public function getReplacements()
    {

        return array_merge(parent::getReplacements(), [
            'controller' => $this->getControllerName(),
            'plural'     => $this->getPluralName(),
            'singular'   => $this->getSingularName(),
            'validator'  => $this->getValidator(),
            'repository' => $this->getRepository(),
            'appname'    => $this->getAppNamespace(),
            // 增加 Module
            'module'     => $this->getModule(),
        ]);
    }
}
