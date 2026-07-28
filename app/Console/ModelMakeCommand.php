<?php

namespace App\Console;

use Illuminate\Support\Str;
use Nwidart\Modules\Commands\Make\ModelMakeCommand as ParentModelMakeCommand;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Override;
use Symfony\Component\Console\Input\InputOption;

class ModelMakeCommand extends ParentModelMakeCommand
{
    #[Override]
    public function handle(): int
    {
        return parent::handle();
    }

    #[Override]
    protected function getDestinationFilePath(): string
    {
        $modules = $this->laravel->make(RepositoryInterface::class);
        $path = $modules->getModulePath($this->getModuleName());

        $modelPath = GenerateConfigReader::read('model');

        return $path . $modelPath->getPath() . '/' . $this->getModelName() . 'Model.php';
    }

    #[Override]
    protected function getOptions(): array
    {
        $options = parent::getOptions();

        return [
            ['factory', 'f', InputOption::VALUE_NONE, 'Create a new factory for the model', null],
            ...$options,
        ];
    }

    #[Override]
    protected function handleOptionalFactoryOption(): void
    {
        if ($this->option('factory') === true) {
            $factoryName = "{$this->getModelName()}";

            $this->call('module:make-factory', array_filter([
                'name' => $factoryName,
                'module' => $this->argument('module'),
            ]));
        }
    }

    private function getModelName(): string
    {
        return Str::studly((string) $this->argument('model'));
    }
}
