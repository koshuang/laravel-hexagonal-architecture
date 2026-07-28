<?php

namespace App\Console;

use Illuminate\Support\Str;
use Nwidart\Modules\Commands\Make\FactoryMakeCommand as ParentFactoryMakeCommand;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Override;

class FactoryMakeCommand extends ParentFactoryMakeCommand
{
    #[Override]
    protected function getDestinationFilePath(): string
    {
        $modules = $this->laravel->make(RepositoryInterface::class);
        $path = $modules->getModulePath($this->getModuleName());

        $factoryPath = GenerateConfigReader::read('factory');

        return $path . $factoryPath->getPath() . '/' . $this->getFileName();
    }

    private function getFileName(): string
    {
        return Str::studly((string) $this->argument('name')) . 'ModelFactory.php';
    }
}
