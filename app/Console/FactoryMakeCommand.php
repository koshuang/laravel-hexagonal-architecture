<?php

namespace App\Console;

use Illuminate\Support\Str;
use Nwidart\Modules\Commands\FactoryMakeCommand as ParentFactoryMakeCommand;
use Nwidart\Modules\Support\Config\GenerateConfigReader;

class FactoryMakeCommand extends ParentFactoryMakeCommand
{
    protected function getDestinationFilePath()
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $factoryPath = GenerateConfigReader::read('factory');

        return $path . $factoryPath->getPath() . '/' . $this->getFileName();
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return Str::studly($this->argument('name')) . 'ModelFactory.php';
    }
}
